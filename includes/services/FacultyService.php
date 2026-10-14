<?php
/**
 * FacultyService - Data Access & Business Logic for Teachers & Student Club
 * 
 * Includes short-lived caching (60s TTL), reordering APIs, and instant cache invalidation.
 */
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../helpers.php';
require_once __DIR__ . '/CacheService.php';

class FacultyService
{
    /* Teachers */

    public static function getHeadTeacher(): ?array
    {
        return CacheService::remember('faculty_teachers_head', 60, function () {
            $res = q("SELECT * FROM teachers WHERE is_head = 1 LIMIT 1")->fetch();
            return $res ?: null;
        });
    }

    public static function getAllTeachers(): array
    {
        return CacheService::remember('faculty_teachers_all', 60, function () {
            return q("
                SELECT * FROM teachers
                ORDER BY is_head DESC, sort_order ASC, id ASC
            ")->fetchAll();
        });
    }

    public static function getTeacherById(int $id): ?array
    {
        $res = q("SELECT * FROM teachers WHERE id = ?", [$id])->fetch();
        return $res ?: null;
    }

    public static function saveTeacher(array $data, ?string $newImage = null): int
    {
        $id             = !empty($data['id']) ? (int) $data['id'] : null;
        $name           = trim((string) ($data['name'] ?? ''));
        $position       = trim((string) ($data['position'] ?? ''));
        $specialization = trim((string) ($data['specialization'] ?? ''));
        $email          = trim((string) ($data['email'] ?? ''));
        $isHead         = !empty($data['is_head']) ? 1 : 0;

        if ($isHead) {
            q("UPDATE teachers SET is_head = 0 WHERE id != ?", [$id ?: 0]);
        }

        if ($id) {
            $existing = q("SELECT sort_order FROM teachers WHERE id = ?", [$id])->fetch();
            $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : (int) ($existing['sort_order'] ?? 0);

            if ($newImage) {
                $old = q("SELECT image_path FROM teachers WHERE id = ?", [$id])->fetch();
                if ($old) delete_upload($old['image_path']);

                q("
                    UPDATE teachers
                    SET name = ?, position = ?, specialization = ?, email = ?, image_path = ?, is_head = ?, sort_order = ?
                    WHERE id = ?
                ", [$name, $position, $specialization, $email, $newImage, $isHead, $sortOrder, $id]);
            } else {
                q("
                    UPDATE teachers
                    SET name = ?, position = ?, specialization = ?, email = ?, is_head = ?, sort_order = ?
                    WHERE id = ?
                ", [$name, $position, $specialization, $email, $isHead, $sortOrder, $id]);
            }
            $resultId = $id;
        } else {
            if (isset($data['sort_order']) && (int) $data['sort_order'] > 0) {
                $sortOrder = (int) $data['sort_order'];
            } else {
                $maxOrder = (int) (q("SELECT MAX(sort_order) AS m FROM teachers")->fetch()['m'] ?? 0);
                $sortOrder = $maxOrder + 1;
            }

            q("
                INSERT INTO teachers (name, position, specialization, email, image_path, is_head, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ", [$name, $position, $specialization, $email, $newImage, $isHead, $sortOrder]);
            $resultId = (int) db()->lastInsertId();
        }

        CacheService::forget('faculty_teachers_all');
        CacheService::forget('faculty_teachers_head');

        return $resultId;
    }

    public static function reorderTeachers(array $orderIds): bool
    {
        $db = db();
        $stmt = $db->prepare("UPDATE teachers SET sort_order = ? WHERE id = ?");
        foreach ($orderIds as $index => $id) {
            $stmt->execute([$index + 1, (int) $id]);
        }

        CacheService::forget('faculty_teachers_all');
        CacheService::forget('faculty_teachers_head');

        return true;
    }

    public static function setHeadTeacher(int $newHeadId): bool
    {
        $db = db();
        $db->beginTransaction();
        try {
            if (!q("SELECT id FROM teachers WHERE id = ?", [$newHeadId])->fetch()) {
                throw new InvalidArgumentException('ไม่พบอาจารย์ที่ต้องการแต่งตั้งในระบบ');
            }

            $currentHead = q("SELECT id FROM teachers WHERE is_head = 1 LIMIT 1")->fetch();
            $oldHeadId = $currentHead ? (int) $currentHead['id'] : null;

            q("UPDATE teachers SET is_head = 0");
            q("UPDATE teachers SET is_head = 1, sort_order = 0 WHERE id = ?", [$newHeadId]);

            if ($oldHeadId && $oldHeadId !== $newHeadId) {
                q("UPDATE teachers SET sort_order = 1 WHERE id = ?", [$oldHeadId]);
            }

            $db->commit();
            CacheService::forget('faculty_teachers_all');
            CacheService::forget('faculty_teachers_head');
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('setHeadTeacher failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function deleteTeacher(int $id): bool
    {
        delete_record_with_asset('teachers', $id);
        CacheService::forget('faculty_teachers_all');
        CacheService::forget('faculty_teachers_head');
        return true;
    }

    /* Student Club */

    public static function getClubPresident(): ?array
    {
        return CacheService::remember('faculty_club_president', 60, function () {
            $res = q("SELECT * FROM club_members WHERE is_club_head = 1 LIMIT 1")->fetch();
            return $res ?: null;
        });
    }

    public static function getAllClubMembers(): array
    {
        return CacheService::remember('faculty_club_all', 60, function () {
            return q("
                SELECT * FROM club_members
                ORDER BY is_club_head DESC, sort_order ASC, id ASC
            ")->fetchAll();
        });
    }

    public static function getClubMemberById(int $id): ?array
    {
        $res = q("SELECT * FROM club_members WHERE id = ?", [$id])->fetch();
        return $res ?: null;
    }

    public static function saveClubMember(array $data, ?string $newImage = null): int
    {
        $id         = !empty($data['id']) ? (int) $data['id'] : null;
        $studentId  = trim((string) ($data['student_id'] ?? ''));
        $name       = trim((string) ($data['name'] ?? ''));
        $clubRole   = trim((string) ($data['club_role'] ?? ''));
        $isClubHead = !empty($data['is_club_head']) ? 1 : 0;

        if ($isClubHead) {
            q("UPDATE club_members SET is_club_head = 0 WHERE id != ?", [$id ?: 0]);
        }

        if ($id) {
            $existing = q("SELECT sort_order FROM club_members WHERE id = ?", [$id])->fetch();
            $sortOrder = isset($data['sort_order']) ? (int) $data['sort_order'] : (int) ($existing['sort_order'] ?? 0);

            if ($newImage) {
                $old = q("SELECT image_path FROM club_members WHERE id = ?", [$id])->fetch();
                if ($old) delete_upload($old['image_path']);

                q("
                    UPDATE club_members
                    SET student_id = ?, name = ?, club_role = ?, is_club_head = ?, image_path = ?, sort_order = ?
                    WHERE id = ?
                ", [$studentId, $name, $clubRole, $isClubHead, $newImage, $sortOrder, $id]);
            } else {
                q("
                    UPDATE club_members
                    SET student_id = ?, name = ?, club_role = ?, is_club_head = ?, sort_order = ?
                    WHERE id = ?
                ", [$studentId, $name, $clubRole, $isClubHead, $sortOrder, $id]);
            }
            $resultId = $id;
        } else {
            if (isset($data['sort_order']) && (int) $data['sort_order'] > 0) {
                $sortOrder = (int) $data['sort_order'];
            } else {
                $maxOrder = (int) (q("SELECT MAX(sort_order) AS m FROM club_members")->fetch()['m'] ?? 0);
                $sortOrder = $maxOrder + 1;
            }

            q("
                INSERT INTO club_members (student_id, name, club_role, is_club_head, image_path, sort_order)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [$studentId, $name, $clubRole, $isClubHead, $newImage, $sortOrder]);
            $resultId = (int) db()->lastInsertId();
        }

        CacheService::forget('faculty_club_all');
        CacheService::forget('faculty_club_president');

        return $resultId;
    }

    public static function reorderClubMembers(array $orderIds): bool
    {
        $db = db();
        $stmt = $db->prepare("UPDATE club_members SET sort_order = ? WHERE id = ?");
        foreach ($orderIds as $index => $id) {
            $stmt->execute([$index + 1, (int) $id]);
        }

        CacheService::forget('faculty_club_all');
        CacheService::forget('faculty_club_president');

        return true;
    }

    public static function setClubPresident(int $newHeadId): bool
    {
        $db = db();
        $db->beginTransaction();
        try {
            if (!q("SELECT id FROM club_members WHERE id = ?", [$newHeadId])->fetch()) {
                throw new InvalidArgumentException('ไม่พบสมาชิกที่ต้องการแต่งตั้งในระบบ');
            }

            $currentHead = q("SELECT id FROM club_members WHERE is_club_head = 1 LIMIT 1")->fetch();
            $oldHeadId = $currentHead ? (int) $currentHead['id'] : null;

            q("UPDATE club_members SET is_club_head = 0");
            q("UPDATE club_members SET is_club_head = 1, sort_order = 0 WHERE id = ?", [$newHeadId]);

            if ($oldHeadId && $oldHeadId !== $newHeadId) {
                q("UPDATE club_members SET sort_order = 1 WHERE id = ?", [$oldHeadId]);
            }

            $db->commit();
            CacheService::forget('faculty_club_all');
            CacheService::forget('faculty_club_president');
            return true;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('setClubPresident failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function deleteClubMember(int $id): bool
    {
        delete_record_with_asset('club_members', $id);
        CacheService::forget('faculty_club_all');
        CacheService::forget('faculty_club_president');
        return true;
    }
}
