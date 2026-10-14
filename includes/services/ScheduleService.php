<?php
/**
 * ScheduleService - Data Access & Business Logic for Timetable & Activities
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/SettingService.php';
require_once __DIR__ . '/CacheService.php';

class ScheduleService
{
    public static function getDefaultPeriods(): array
    {
        return [
            ['num' => 0,  'start' => '08:00', 'end' => '08:30', 'label' => '08:00-08:30', 'type' => 'class', 'name' => 'กิจกรรม'],
            ['num' => 1,  'start' => '08:30', 'end' => '09:30', 'label' => '08:30-09:30', 'type' => 'class', 'name' => 'คาบที่ 1'],
            ['num' => 2,  'start' => '09:30', 'end' => '10:30', 'label' => '09:30-10:30', 'type' => 'class', 'name' => 'คาบที่ 2'],
            ['num' => 3,  'start' => '10:30', 'end' => '11:30', 'label' => '10:30-11:30', 'type' => 'class', 'name' => 'คาบที่ 3'],
            ['num' => 4,  'start' => '11:30', 'end' => '12:30', 'label' => '11:30-12:30', 'type' => 'class', 'name' => 'คาบที่ 4'],
            ['num' => 5,  'start' => '12:30', 'end' => '13:30', 'label' => '12:30-13:30', 'type' => 'class', 'name' => 'พักกลางวัน'],
            ['num' => 6,  'start' => '13:30', 'end' => '14:30', 'label' => '13:30-14:30', 'type' => 'class', 'name' => 'คาบที่ 6'],
            ['num' => 7,  'start' => '14:30', 'end' => '15:30', 'label' => '14:30-15:30', 'type' => 'class', 'name' => 'คาบที่ 7'],
            ['num' => 8,  'start' => '15:30', 'end' => '16:30', 'label' => '15:30-16:30', 'type' => 'class', 'name' => 'คาบที่ 8'],
            ['num' => 9,  'start' => '16:30', 'end' => '17:30', 'label' => '16:30-17:30', 'type' => 'class', 'name' => 'คาบที่ 9'],
            ['num' => 10, 'start' => '17:30', 'end' => '18:30', 'label' => '17:30-18:30', 'type' => 'class', 'name' => 'คาบที่ 10'],
        ];
    }

    public static function restoreDefaultSpecialSlots(string $group = 'ปวช.2/1'): int
    {
        if ($group === '') {
            $group = 'ปวช.2/1';
        }
        $inserted = 0;
        for ($d = 1; $d <= 5; $d++) {
            $hasFlag = q("SELECT id FROM class_schedule WHERE class_group = ? AND day_of_week = ? AND (subject_code = 'FLAG' OR subject_name LIKE '%เสาธง%')", [$group, $d])->fetch();
            if (!$hasFlag) {
                $collision = q("SELECT id FROM class_schedule WHERE class_group = ? AND day_of_week = ? AND time_start < '08:30:00' AND time_end > '08:00:00'", [$group, $d])->fetch();
                if (!$collision) {
                    q("INSERT INTO class_schedule (day_of_week, time_start, time_end, subject_code, subject_name, teacher_name, room, class_group)
                       VALUES (?, '08:00:00', '08:30:00', 'FLAG', 'กิจกรรมหน้าเสาธง', '', '', ?)", [$d, $group]);
                    $inserted++;
                }
            }

            $hasLunch = q("SELECT id FROM class_schedule WHERE class_group = ? AND day_of_week = ? AND (subject_code = 'LUNCH' OR subject_name LIKE '%พักกลางวัน%')", [$group, $d])->fetch();
            if (!$hasLunch) {
                $collision = q("SELECT id FROM class_schedule WHERE class_group = ? AND day_of_week = ? AND time_start < '13:30:00' AND time_end > '12:30:00'", [$group, $d])->fetch();
                if (!$collision) {
                    q("INSERT INTO class_schedule (day_of_week, time_start, time_end, subject_code, subject_name, teacher_name, room, class_group)
                       VALUES (?, '12:30:00', '13:30:00', 'LUNCH', 'พักกลางวัน', '', '', ?)", [$d, $group]);
                    $inserted++;
                }
            }
        }
        if ($inserted > 0) {
            CacheService::forgetPattern('schedule_');
        }
        return $inserted;
    }

    public static function ensureSpecialSlots(string $group = 'ปวช.2/1'): void
    {
        self::restoreDefaultSpecialSlots($group);
    }

    public static function getPeriods(): array
    {
        $json = SettingService::get('schedule_periods');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return self::getDefaultPeriods();
    }

    public static function savePeriods(array $periods): void
    {
        $clean = [];
        foreach ($periods as $p) {
            $num   = (int) ($p['num'] ?? 0);
            $start = trim((string) ($p['start'] ?? ''));
            $end   = trim((string) ($p['end'] ?? ''));
            $type  = in_array($p['type'] ?? 'class', ['class', 'lunch', 'flag'], true) ? $p['type'] : 'class';
            $name  = trim((string) ($p['name'] ?? ''));
            if ($start !== '' && $end !== '' && strtotime("1970-01-01 $start") < strtotime("1970-01-01 $end")) {
                $clean[] = [
                    'num'   => $num,
                    'start' => $start,
                    'end'   => $end,
                    'label' => "$start-$end",
                    'type'  => $type,
                    'name'  => $name,
                ];
            }
        }
        SettingService::set('schedule_periods', json_encode($clean, JSON_UNESCAPED_UNICODE));
        CacheService::forgetPattern('schedule_grid_');
    }

    public static function updateSpecialPeriod(int $periodNum, string $newStart, string $newEnd, string $specialType = ''): array
    {
        $periods = self::getPeriods();
        if (empty($periods)) {
            $periods = self::getDefaultPeriods();
        }

        // 1. Identify the special type (lunch or flag)
        if ($specialType === '') {
            foreach ($periods as $p) {
                if ((int) ($p['num'] ?? -999) === $periodNum) {
                    $specialType = $p['type'] ?? '';
                    break;
                }
            }
        }
        if (!in_array($specialType, ['lunch', 'flag'], true)) {
            $specialType = 'lunch';
        }

        $normNewStart = substr($newStart, 0, 5);
        $normNewEnd   = substr($newEnd, 0, 5);
        $tNewStart    = strtotime("1970-01-01 {$normNewStart}:00");
        $tNewEnd      = strtotime("1970-01-01 {$normNewEnd}:00");

        if ($tNewEnd <= $tNewStart) {
            throw new InvalidArgumentException("เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น ({$normNewStart} - {$normNewEnd})");
        }

        // 2. Clear previous instances of this special type, reverting them to normal class
        foreach ($periods as $idx => $p) {
            if (($p['type'] ?? '') === $specialType) {
                $pNum = (int) ($p['num'] ?? $idx);
                $periods[$idx]['type'] = 'class';
                $periods[$idx]['name'] = ($pNum === 0) ? 'กิจกรรม' : "คาบที่ {$pNum}";
            }
        }

        // 3. Mark all periods within the new [normNewStart, normNewEnd] range as this special type
        $markedCount = 0;
        foreach ($periods as $idx => $p) {
            $pStart = strtotime("1970-01-01 " . substr($p['start'], 0, 5) . ":00");
            $pEnd   = strtotime("1970-01-01 " . substr($p['end'], 0, 5) . ":00");

            if ($pStart >= $tNewStart && $pEnd <= $tNewEnd) {
                $periods[$idx]['type'] = $specialType;
                $periods[$idx]['name'] = ($specialType === 'flag') ? 'กิจกรรมหน้าเสาธง' : 'พักกลางวัน';
                $markedCount++;
            }
        }

        // Fallback: If no period matched within range, mark the intersecting period
        if ($markedCount === 0) {
            foreach ($periods as $idx => $p) {
                $pStart = strtotime("1970-01-01 " . substr($p['start'], 0, 5) . ":00");
                $pEnd   = strtotime("1970-01-01 " . substr($p['end'], 0, 5) . ":00");
                if ($pStart == $tNewStart || ($pStart < $tNewEnd && $pEnd > $tNewStart)) {
                    $periods[$idx]['type'] = $specialType;
                    $periods[$idx]['name'] = ($specialType === 'flag') ? 'กิจกรรมหน้าเสาธง' : 'พักกลางวัน';
                    $markedCount++;
                    break;
                }
            }
        }

        // 4. Save cleanly preserved periods (no duplicate intervals, continuous)
        self::savePeriods($periods);

        return [
            'type'  => $specialType,
            'start' => $normNewStart,
            'end'   => $normNewEnd,
        ];
    }

    public static function getAvailableGroups(): array
    {
        return q("
            SELECT DISTINCT class_group
            FROM class_schedule
            WHERE class_group IS NOT NULL AND class_group != ''
            ORDER BY class_group ASC
        ")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getScheduleByDay(string $group = ''): array
    {
        $selectFields = "
            s.id,
            s.class_id,
            s.day_of_week,
            s.time_start,
            s.time_end,
            s.class_group,
            COALESCE(c.subject_code, s.subject_code) AS subject_code,
            COALESCE(c.subject_name, s.subject_name) AS subject_name,
            COALESCE(c.course_type, 'regular') AS course_type,
            COALESCE(NULLIF(s.teacher_name, ''), c.default_teacher, '') AS teacher_name,
            COALESCE(NULLIF(s.room, ''), c.default_room, '') AS room,
            COALESCE(c.credit, 3) AS credit
        ";

        if (!empty($group) && $group !== 'all') {
            $slots = q("
                SELECT {$selectFields}
                FROM class_schedule s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.class_group = ?
                ORDER BY s.day_of_week ASC, s.time_start ASC
            ", [$group])->fetchAll();
        } else {
            $slots = q("
                SELECT {$selectFields}
                FROM class_schedule s
                LEFT JOIN classes c ON s.class_id = c.id
                ORDER BY s.day_of_week ASC, s.time_start ASC
            ")->fetchAll();
        }

        $byDay = [1 => [], 2 => [], 3 => [], 4 => [], 5 => []];
        foreach ($slots as $slot) {
            $d = (int) $slot['day_of_week'];
            if (isset($byDay[$d])) {
                $byDay[$d][] = $slot;
            }
        }
        return $byDay;
    }

    /**
     * Builds deduplicated vocational grid matching official สอศ. standard.
     * Days (1-5) as rows, Periods as columns, multi-hour slots as merged single blocks via colspan.
     */
    public static function getTimetableGrid(string $group = ''): array
    {
        if ($group === '') {
            $group = 'ปวช.2/1';
        }
        // Pure read query: automatic ensureSpecialSlots removed to prevent zombie slots

        $periods = self::getPeriods();
        $byDay   = self::getScheduleByDay($group);
        $grid    = [];

        foreach ([1, 2, 3, 4, 5] as $day) {
            $daySlots = $byDay[$day] ?? [];
            $cells = [];
            $pCount = count($periods);
            $pIdx = 0;
            $placedSlotIds = [];

            while ($pIdx < $pCount) {
                $period = $periods[$pIdx];
                $pStart = strtotime("1970-01-01 {$period['start']}:00");
                $pEnd   = strtotime("1970-01-01 {$period['end']}:00");

                $matchingSlot = self::findMatchingSlot($daySlots, $pStart, $pEnd, $placedSlotIds);

                if ($matchingSlot) {
                    $placedSlotIds[$matchingSlot['id']] = true;
                    $sEnd = strtotime("1970-01-01 {$matchingSlot['time_end']}");
                    $span = self::calculateSlotColspan($periods, $pIdx, $sEnd);

                    $cells[] = self::createGridCell($matchingSlot, $period, $span);
                    $pIdx += $span;
                } else {
                    $cells[] = [
                        'type'     => 'empty',
                        'colspan'  => 1,
                        'period'   => $period,
                        'day'      => $day,
                    ];
                    $pIdx++;
                }
            }

            $grid[$day] = $cells;
        }

        return [
            'periods' => $periods,
            'grid'    => $grid,
        ];
    }

    private static function findMatchingSlot(array $daySlots, int $pStart, int $pEnd, array $placedSlotIds): ?array
    {
        foreach ($daySlots as $slot) {
            if (isset($placedSlotIds[$slot['id']])) {
                continue;
            }
            $sStart = strtotime("1970-01-01 {$slot['time_start']}");
            $sEnd   = strtotime("1970-01-01 {$slot['time_end']}");

            if ($sStart >= $pStart && $sStart < $pEnd) {
                return $slot;
            }
            if ($sStart < $pStart && $sEnd > $pStart) {
                return $slot;
            }
        }
        return null;
    }

    private static function calculateSlotColspan(array $periods, int $pIdx, int $sEnd): int
    {
        $pCount = count($periods);
        $span = 0;
        for ($k = $pIdx; $k < $pCount; $k++) {
            $nextP = $periods[$k];
            $npStart = strtotime("1970-01-01 {$nextP['start']}:00");
            if ($npStart < $sEnd) {
                $span++;
            } else {
                break;
            }
        }
        $span = max(1, $span);
        return min($span, $pCount - $pIdx);
    }

    private static function createGridCell(array $slot, array $period, int $span): array
    {
        $code = strtoupper(trim((string) $slot['subject_code']));
        $name = trim((string) $slot['subject_name']);

        $isFlag  = ($code === 'FLAG' || str_contains($name, 'เสาธง'));
        $isLunch = ($code === 'LUNCH' || str_contains($name, 'พักกลางวัน') || str_contains($name, 'พักเที่ยง'));

        if ($isFlag) {
            return [
                'type'    => 'flag',
                'colspan' => $span,
                'period'  => $period,
                'slot'    => $slot,
                'title'   => $name ?: 'กิจกรรมหน้าเสาธง',
            ];
        }
        if ($isLunch) {
            return [
                'type'    => 'lunch',
                'colspan' => $span,
                'period'  => $period,
                'slot'    => $slot,
                'title'   => $name ?: 'พักกลางวัน',
            ];
        }
        return [
            'type'    => 'class',
            'colspan' => $span,
            'period'  => $period,
            'slot'    => $slot,
        ];
    }

    public static function getAllClassSlots(): array
    {
        return q("
            SELECT * FROM class_schedule
            ORDER BY day_of_week ASC, time_start ASC
        ")->fetchAll();
    }

    public static function getAllClasses(): array
    {
        return self::getClasses();
    }

    /* --------------------------------------------------------------------------
     * Master Courses Catalog (classes table)
     * -------------------------------------------------------------------------- */

    public static function getClasses(?string $prefix = null, ?string $courseType = null): array
    {
        $sql = "SELECT * FROM classes WHERE 1=1";
        $params = [];
        if (!empty($prefix) && $prefix !== 'all') {
            $sql .= " AND subject_code LIKE ?";
            $params[] = $prefix . '%';
        }
        if (!empty($courseType) && $courseType !== 'all') {
            $sql .= " AND course_type = ?";
            $params[] = $courseType;
        }
        $sql .= " ORDER BY subject_code ASC";
        return q($sql, $params)->fetchAll();
    }

    public static function getClassById(int $id): ?array
    {
        $res = q("SELECT * FROM classes WHERE id = ?", [$id])->fetch();
        return $res ?: null;
    }

    public static function getClassByCode(string $code): ?array
    {
        $res = q("SELECT * FROM classes WHERE subject_code = ?", [trim($code)])->fetch();
        return $res ?: null;
    }

    public static function saveClass(array $data): int
    {
        $id             = !empty($data['id']) ? (int) $data['id'] : null;
        $subjectCode    = clean_text(trim((string) ($data['subject_code'] ?? '')));
        $subjectName    = clean_text(trim((string) ($data['subject_name'] ?? '')));
        $courseType     = in_array($data['course_type'] ?? '', ['professional', 'regular'], true) ? $data['course_type'] : 'professional';
        $defaultTeacher = clean_text(trim((string) ($data['default_teacher'] ?? '')));
        $defaultRoom    = clean_text(trim((string) ($data['default_room'] ?? '')));
        $credit         = max(1, min(6, (int) ($data['credit'] ?? 3)));

        if ($subjectCode === '' || $subjectName === '') {
            throw new InvalidArgumentException("กรุณากรอกรหัสวิชาและชื่อวิชาให้ครบถ้วน");
        }

        // Duplicate code check
        $dupCheck = q("SELECT id FROM classes WHERE subject_code = ? AND id != ?", [$subjectCode, $id ?: 0])->fetch();
        if ($dupCheck) {
            throw new RuntimeException("รหัสวิชา '{$subjectCode}' นี้มีอยู่ในระบบแล้ว");
        }

        if ($id) {
            q("
                UPDATE classes
                SET subject_code = ?, subject_name = ?, course_type = ?, default_teacher = ?, default_room = ?, credit = ?
                WHERE id = ?
            ", [$subjectCode, $subjectName, $courseType, $defaultTeacher, $defaultRoom, $credit, $id]);

            // Synchronize fallback columns in class_schedule for this class_id
            q("
                UPDATE class_schedule
                SET subject_code = ?, subject_name = ?
                WHERE class_id = ?
            ", [$subjectCode, $subjectName, $id]);

            $savedId = $id;
        } else {
            q("
                INSERT INTO classes (subject_code, subject_name, course_type, default_teacher, default_room, credit)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [$subjectCode, $subjectName, $courseType, $defaultTeacher, $defaultRoom, $credit]);
            $savedId = (int) db()->lastInsertId();
        }

        CacheService::forgetPattern('schedule_');
        CacheService::forgetPattern('home_stats_');
        return $savedId;
    }

    public static function deleteClass(int $id): bool
    {
        // Guard: Check if class is placed in active schedules
        $inUseCount = (int) q("SELECT COUNT(*) FROM class_schedule WHERE class_id = ?", [$id])->fetchColumn();
        if ($inUseCount > 0) {
            throw new RuntimeException("ไม่สามารถลบวิชานี้ได้ เนื่องจากมีคาบเรียนผูกอยู่ในตารางเรียนจำนวน {$inUseCount} คาบ กรุณาย้ายหรือลบคาบเรียนที่เกี่ยวข้องก่อน");
        }

        q("DELETE FROM classes WHERE id = ?", [$id]);
        CacheService::forgetPattern('schedule_');
        CacheService::forgetPattern('home_stats_');
        return true;
    }

    public static function getClassPrefixes(): array
    {
        $rows = q("SELECT DISTINCT SUBSTRING_INDEX(subject_code, '-', 1) AS prefix FROM classes WHERE subject_code LIKE '%-%' ORDER BY prefix ASC")->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_filter($rows));
    }

    public static function getProfessionalCourseCount(): int
    {
        return (int) q("SELECT COUNT(*) FROM classes WHERE course_type = 'professional'")->fetchColumn();
    }

    /* --------------------------------------------------------------------------
     * Timetable Slots (class_schedule table)
     * -------------------------------------------------------------------------- */

    public static function getClassSlotById(int $id): ?array
    {
        $res = q("
            SELECT 
                s.id,
                s.class_id,
                s.day_of_week,
                s.time_start,
                s.time_end,
                s.class_group,
                COALESCE(c.subject_code, s.subject_code) AS subject_code,
                COALESCE(c.subject_name, s.subject_name) AS subject_name,
                COALESCE(c.course_type, 'regular') AS course_type,
                COALESCE(NULLIF(s.teacher_name, ''), c.default_teacher, '') AS teacher_name,
                COALESCE(NULLIF(s.room, ''), c.default_room, '') AS room,
                COALESCE(c.credit, 3) AS credit
            FROM class_schedule s
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE s.id = ?
        ", [$id])->fetch();
        return $res ?: null;
    }

    public static function updateSlotPosition(int $slotId, int $newDay, string $newTimeStart, string $newTimeEnd): array
    {
        $slot = self::getClassSlotById($slotId);
        if (!$slot) {
            throw new RuntimeException("ไม่พบข้อมูลคาบเรียนรหัส #{$slotId}");
        }

        if ($newDay < 1 || $newDay > 5) {
            throw new InvalidArgumentException("วันของสัปดาห์ไม่ถูกต้อง (1-5)");
        }

        $tStart = strtotime("1970-01-01 $newTimeStart");
        $tEnd   = strtotime("1970-01-01 $newTimeEnd");
        if (!$tStart || !$tEnd || $tStart >= $tEnd) {
            throw new InvalidArgumentException("ช่วงเวลาไม่ถูกต้อง (เวลาเริ่มต้องน้อยกว่าเวลาสิ้นสุด)");
        }

        // Collision check for the same class group
        $collision = q("
            SELECT id, subject_code, subject_name, time_start, time_end
            FROM class_schedule
            WHERE class_group = ? AND day_of_week = ? AND id != ?
              AND time_start < ? AND time_end > ?
            LIMIT 1
        ", [$slot['class_group'], $newDay, $slotId, $newTimeEnd, $newTimeStart])->fetch();

        if ($collision) {
            $cName = $collision['subject_name'] ?: $collision['subject_code'];
            $cCode = $collision['subject_code'];
            $tStartFmt = hm($collision['time_start']);
            $tEndFmt = hm($collision['time_end']);
            throw new RuntimeException("เวลาซ้อนทับกับ {$cName} [{$cCode}] ({$tStartFmt}–{$tEndFmt} น.)");
        }

        q("
            UPDATE class_schedule
            SET day_of_week = ?, time_start = ?, time_end = ?
            WHERE id = ?
        ", [$newDay, $newTimeStart, $newTimeEnd, $slotId]);

        CacheService::forgetPattern('schedule_');
        return self::getClassSlotById($slotId);
    }

    public static function saveClassSlot(array $data): int
    {
        $id          = !empty($data['id']) ? (int) $data['id'] : null;
        $classId     = !empty($data['class_id']) ? (int) $data['class_id'] : null;
        $dayOfWeek   = (int) ($data['day_of_week'] ?? 1);
        $timeStart   = trim((string) ($data['time_start'] ?? '08:30'));
        $timeEnd     = trim((string) ($data['time_end'] ?? '10:30'));
        $subjectCode = clean_text(trim((string) ($data['subject_code'] ?? '')));
        $subjectName = clean_text(trim((string) ($data['subject_name'] ?? '')));
        $teacherName = clean_text(trim((string) ($data['teacher_name'] ?? '')));
        $room        = clean_text(trim((string) ($data['room'] ?? '')));
        $classGroup  = clean_text(trim((string) ($data['class_group'] ?? 'ปวช.2/1')));

        // If class_id is provided, populate defaults from master if fields are empty
        if ($classId) {
            $course = self::getClassById($classId);
            if ($course) {
                if ($subjectCode === '') $subjectCode = $course['subject_code'];
                if ($subjectName === '') $subjectName = $course['subject_name'];
                if ($teacherName === '') $teacherName = $course['default_teacher'] ?? '';
                if ($room === '') $room = $course['default_room'] ?? '';
            }
        }

        // Check for collision in the same group
        $collision = q("
            SELECT id, subject_code, subject_name
            FROM class_schedule
            WHERE class_group = ? AND day_of_week = ? AND id != ?
              AND time_start < ? AND time_end > ?
            LIMIT 1
        ", [$classGroup, $dayOfWeek, $id ?: 0, $timeEnd, $timeStart])->fetch();

        if ($collision) {
            throw new RuntimeException("เวลาซ้อนทับกับวิชา {$collision['subject_code']} {$collision['subject_name']}");
        }

        if ($id) {
            q("
                UPDATE class_schedule
                SET class_id = ?, day_of_week = ?, time_start = ?, time_end = ?, subject_code = ?, subject_name = ?, teacher_name = ?, room = ?, class_group = ?
                WHERE id = ?
            ", [$classId, $dayOfWeek, $timeStart, $timeEnd, $subjectCode, $subjectName, $teacherName, $room, $classGroup, $id]);
            $savedId = $id;
        } else {
            q("
                INSERT INTO class_schedule (class_id, day_of_week, time_start, time_end, subject_code, subject_name, teacher_name, room, class_group)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$classId, $dayOfWeek, $timeStart, $timeEnd, $subjectCode, $subjectName, $teacherName, $room, $classGroup]);
            $savedId = (int) db()->lastInsertId();
        }

        CacheService::forgetPattern('schedule_');
        return $savedId;
    }

    public static function deleteClassSlot(int $id): bool
    {
        q("DELETE FROM class_schedule WHERE id = ?", [$id]);
        CacheService::forgetPattern('schedule_');
        return true;
    }

    /* Activities */

    public static function getNextUpcomingActivity(): ?array
    {
        $res = q("
            SELECT * FROM activities
            WHERE event_date >= CURDATE()
            ORDER BY event_date ASC, time_start ASC
            LIMIT 1
        ")->fetch();
        return $res ?: null;
    }

    public static function getActivitiesGroupedByMonth(): array
    {
        return CacheService::remember('activities_grouped', 300, function () {
            $activities = q("
                SELECT * FROM activities
                ORDER BY event_date ASC, time_start ASC
            ")->fetchAll();

            $grouped = [];
            foreach ($activities as $act) {
                $ym = date('Y-m', strtotime($act['event_date']));
                $grouped[$ym][] = $act;
            }
            return $grouped;
        });
    }

    public static function getAllActivities(): array
    {
        return q("
            SELECT * FROM activities
            ORDER BY event_date DESC, id DESC
        ")->fetchAll();
    }

    public static function getActivityById(int $id): ?array
    {
        $res = q("SELECT * FROM activities WHERE id = ?", [$id])->fetch();
        return $res ?: null;
    }

    public static function saveActivity(array $data): int
    {
        $id           = !empty($data['id']) ? (int) $data['id'] : null;
        $title        = clean_text(trim((string) ($data['title'] ?? '')));
        $description  = clean_text(trim((string) ($data['description'] ?? '')));
        $eventDate    = trim((string) ($data['event_date'] ?? ''));
        $eventDateEnd = trim((string) ($data['event_date_end'] ?? '')) ?: null;
        $timeStart    = trim((string) ($data['time_start'] ?? '')) ?: null;
        $timeEnd      = trim((string) ($data['time_end'] ?? '')) ?: null;
        $location     = clean_text(trim((string) ($data['location'] ?? '')));
        $actionUrl    = safe_url(trim((string) ($data['action_url'] ?? '')));

        if ($id) {
            q("
                UPDATE activities
                SET title = ?, description = ?, event_date = ?, event_date_end = ?, time_start = ?, time_end = ?, location = ?, action_url = ?
                WHERE id = ?
            ", [$title, $description, $eventDate, $eventDateEnd, $timeStart, $timeEnd, $location, $actionUrl ?: null, $id]);
            CacheService::forget('activities_grouped');
            return $id;
        } else {
            q("
                INSERT INTO activities (title, description, event_date, event_date_end, time_start, time_end, location, action_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [$title, $description, $eventDate, $eventDateEnd, $timeStart, $timeEnd, $location, $actionUrl ?: null]);
            $newActId = (int) db()->lastInsertId();
            CacheService::forget('activities_grouped');
            return $newActId;
        }
    }

    public static function deleteActivity(int $id): bool
    {
        q("DELETE FROM activities WHERE id = ?", [$id]);
        CacheService::forget('activities_grouped');
        return true;
    }
}
