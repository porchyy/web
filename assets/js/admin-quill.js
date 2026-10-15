/**
 * admin-quill.js - Modernized Quill Editor
 * Standard: STYLE.md & RULES.md
 * 
 * Features:
 * - Smart Heading Block Splitting: Applying a header to a sub-phrase isolates it cleanly into its own heading block instead of corrupting the surrounding paragraph.
 * - Automatic Keystroke Markdown: (#, ##, ###, -, 1., >) instantly formats lines.
 * - Inline Markdown Shortcuts: (**text**, *text*, ~~text~~) format in place without wiping DOM or losing cursor.
 * - Seamless Paste Interception: Pasted markdown and HTML tags are converted to Delta cleanly.
 * - Non-destructive: NO full-DOM dangerouslyPasteHTML wipes on keystrokes.
 * - Clean sync: Automatically updates hidden input on every change and on form submit.
 */

(function () {
  'use strict';

  /**
   * Parse inline Markdown syntax into HTML
   */
  function parseInlineMarkdown(text) {
    if (!text || typeof text !== 'string') return '';
    let res = text;

    // Bold: **text** or __text__
    res = res.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    res = res.replace(/__(.+?)__/g, '<strong>$1</strong>');

    // Strikethrough: ~~text~~
    res = res.replace(/~~(.+?)~~/g, '<s>$1</s>');

    // Italic: *text* or _text_
    res = res.replace(/(^|[^\*])\*([^\*\s][^\*]*?)\*(?!\*)/g, '$1<em>$2</em>');
    res = res.replace(/(^|[^_])_([^_\s][^_]*?)_(?!_)/g, '$1<em>$2</em>');

    // Links: [title](url) with protocol validation
    res = res.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (match, title, rawUrl) => {
      const cleanUrl = (rawUrl || '').trim();
      const isSafe = /^(?:https?:\/\/|mailto:|\/|#|\.\/)/i.test(cleanUrl) && !/^javascript:/i.test(cleanUrl);
      const safeHref = isSafe ? cleanUrl.replace(/"/g, '&quot;') : '#';
      return `<a href="${safeHref}" target="_blank" rel="noopener noreferrer">${title}</a>`;
    });

    // Inline code: `code`
    res = res.replace(/`([^`]+)`/g, '<code>$1</code>');

    return res;
  }

  /**
   * Full Markdown & HTML Parser for initial loading and final serialization
   */
  function parseMarkdownAndHtml(rawInput) {
    if (!rawInput || typeof rawInput !== 'string') return '';
    let str = rawInput.trim();
    if (!str) return '';

    // Decode HTML-encoded tags if present
    if (/&lt;[a-z0-9]+(\s[^&]*)?&gt;/i.test(str) || /&lt;\/[a-z0-9]+&gt;/i.test(str)) {
      const doc = new DOMParser().parseFromString(str, 'text/html');
      str = doc.documentElement.textContent || str;
    }

    str = str.replace(/&amp;([a-zA-Z0-9#]+;)/g, '&$1');
    str = str.replace(/&nbsp;/g, ' ');

    const lines = str.split(/\r?\n/);
    const parsedBlocks = [];
    let inUl = false;
    let inOl = false;

    for (let i = 0; i < lines.length; i++) {
      let line = lines[i];

      // Bullet list
      if (/^\s*[-*]\s+(.+)$/.test(line) && !/^\s*\*\*/.test(line)) {
        const itemText = line.replace(/^\s*[-*]\s+/, '');
        if (!inUl) {
          if (inOl) { parsedBlocks.push('</ol>'); inOl = false; }
          parsedBlocks.push('<ul>');
          inUl = true;
        }
        parsedBlocks.push(`<li>${parseInlineMarkdown(itemText)}</li>`);
        continue;
      }

      // Numbered list
      if (/^\s*\d+\.\s+(.+)$/.test(line)) {
        const itemText = line.replace(/^\s*\d+\.\s+/, '');
        if (!inOl) {
          if (inUl) { parsedBlocks.push('</ul>'); inUl = false; }
          parsedBlocks.push('<ol>');
          inOl = true;
        }
        parsedBlocks.push(`<li>${parseInlineMarkdown(itemText)}</li>`);
        continue;
      }

      if (inUl) { parsedBlocks.push('</ul>'); inUl = false; }
      if (inOl) { parsedBlocks.push('</ol>'); inOl = false; }

      // Headings
      if (/^###\s+(.+)$/.test(line)) {
        parsedBlocks.push(`<h4>${parseInlineMarkdown(line.replace(/^###\s+/, ''))}</h4>`);
        continue;
      }
      if (/^##\s+(.+)$/.test(line)) {
        parsedBlocks.push(`<h3>${parseInlineMarkdown(line.replace(/^##\s+/, ''))}</h3>`);
        continue;
      }
      if (/^#\s+(.+)$/.test(line)) {
        parsedBlocks.push(`<h2>${parseInlineMarkdown(line.replace(/^#\s+/, ''))}</h2>`);
        continue;
      }

      // Blockquotes
      if (/^>\s+(.+)$/.test(line)) {
        parsedBlocks.push(`<blockquote>${parseInlineMarkdown(line.replace(/^>\s+/, ''))}</blockquote>`);
        continue;
      }

      // Standard paragraph lines
      if (line.trim() !== '') {
        const parsedLine = parseInlineMarkdown(line);
        if (/^<[a-z0-9]+/i.test(parsedLine.trim())) {
          parsedBlocks.push(parsedLine);
        } else {
          parsedBlocks.push(`<p>${parsedLine}</p>`);
        }
      }
    }

    if (inUl) parsedBlocks.push('</ul>');
    if (inOl) parsedBlocks.push('</ol>');

    let output = parsedBlocks.join('\n');
    if (!output.trim()) {
      output = parseInlineMarkdown(str);
      if (output && !/^<[a-z0-9]+/i.test(output.trim())) {
        output = `<p>${output}</p>`;
      }
    }

    return output;
  }

  /**
   * Serialize Quill content into clean, standard HTML for MySQL storage
   */
  function parseQuillToHtml(quill) {
    if (!quill) return '';
    let rawHtml = quill.root.innerHTML;
    const textOnly = quill.getText().trim();

    if (!textOnly && (rawHtml === '<p><br></p>' || rawHtml === '<p></p>' || !rawHtml.trim())) {
      return '';
    }

    // Apply inline markdown cleanup to any residual text
    rawHtml = parseInlineMarkdown(rawHtml);
    return rawHtml;
  }

  /**
   * Initialize a Quill Editor instance on container
   */
  function initQuillInstance(containerEl) {
    if (!containerEl || containerEl.__quillInitialized) return;
    if (typeof window.Quill === 'undefined') {
      console.warn('[Quill] window.Quill is not loaded. Skipping initialization.');
      return;
    }

    const targetInputId = containerEl.dataset.targetInput;
    let targetInput = targetInputId ? document.getElementById(targetInputId) : null;
    if (!targetInput) {
      targetInput = containerEl.closest('form')?.querySelector('textarea[name="content"], input[name="content"]');
    }
    if (!targetInput) return;

    const isTitleMode = containerEl.id === 'quill-hero-title-editor' || containerEl.dataset.type === 'title';

    // Toolbar Selection: Title Mode vs Standard Editorial
    const toolbarOptions = isTitleMode ? [
      ['bold', 'italic', 'clean']
    ] : [
      [{ 'header': [2, 3, 4, false] }],
      ['bold', 'italic', 'underline', 'strike'],
      [{ 'list': 'ordered' }, { 'list': 'bullet' }],
      ['blockquote', 'code-block'],
      ['link', 'clean']
    ];

    const quill = new window.Quill(containerEl, {
      theme: 'snow',
      placeholder: containerEl.dataset.placeholder || 'พิมพ์เนื้อหาที่นี่...',
      modules: {
        toolbar: toolbarOptions
      }
    });

    containerEl.__quillInitialized = true;
    containerEl.classList.add('quill-editorial-wrapper');

    // 1. SMART HEADING HANDLER: Partial text selection splits block cleanly
    const toolbar = quill.getModule('toolbar');
    if (toolbar) {
      toolbar.addHandler('header', function (value) {
        const range = quill.getSelection();
        if (!range) return;

        // If collapsed cursor (no text selected) or reset to normal text
        if (range.length === 0 || value === false) {
          quill.format('header', value, 'user');
          return;
        }

        const [line, offset] = quill.getLine(range.index);
        if (line) {
          const lineLength = line.length(); // includes line-break \n

          // Check if selection is a partial sub-phrase within the block
          if (offset > 0 || range.length < lineLength - 1) {
            let targetIndex = range.index;
            let targetLength = range.length;

            // If selection is before the end of the block, split after
            if (offset + range.length < lineLength - 1) {
              quill.insertText(range.index + range.length, '\n', 'user');
            }

            // If selection is after the start of the block, split before
            if (offset > 0) {
              quill.insertText(range.index, '\n', 'user');
              targetIndex += 1;
            }

            quill.setSelection(targetIndex, targetLength, 'silent');
            quill.formatLine(targetIndex, targetLength, 'header', value, 'user');
            return;
          }
        }

        // Entire block selected -> standard format
        quill.format('header', value, 'user');
      });
    }

  /**
   * Safe DOM Sanitizer & Renderer (Ponytail / Zero external dependencies)
   * Constructs clean, native DOM nodes from HTML or Quill content in an inert Document.
   * Completely eliminates innerHTML on live elements and prevents XSS.
   *
   * @param {HTMLElement} targetEl - Destination DOM element
   * @param {string} content - Raw HTML or text with tags/line breaks
   * @param {Object} [options]
   * @param {boolean} [options.isTitle] - Title mode: preserves line breaks as <br>, unwraps block tags
   * @param {string} [options.fallback] - Fallback text if content is empty
   */
  function renderSafeHtml(targetEl, content, options = {}) {
    if (!targetEl || typeof targetEl.appendChild !== 'function') return;

    const isTitle = Boolean(options.isTitle);
    const raw = typeof content === 'string' ? content.trim() : '';

    while (targetEl.firstChild) {
      targetEl.removeChild(targetEl.firstChild);
    }

    if (!raw) {
      if (options.fallback) {
        targetEl.appendChild(document.createTextNode(options.fallback));
      }
      return;
    }

    let htmlToParse = raw;
    if (isTitle) {
      htmlToParse = htmlToParse.replace(/\r?\n/g, '<br>');
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlToParse, 'text/html');

    const allowedTitleTags = new Set(['strong', 'b', 'em', 'i', 'u', 's', 'span', 'br']);
    const allowedBodyTags = new Set([
      'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'strike', 'hr',
      'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote',
      'code', 'pre', 'a', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td'
    ]);
    const allowedTags = isTitle ? allowedTitleTags : allowedBodyTags;

    const dangerousTags = new Set([
      'script', 'style', 'iframe', 'frame', 'object', 'embed', 'applet',
      'svg', 'math', 'form', 'input', 'button', 'select', 'textarea', 'option',
      'link', 'meta', 'base'
    ]);

    function isSafeUrl(url) {
      if (!url) return false;
      const clean = url.trim();
      return /^(?:https?:\/\/|mailto:|tel:|\/|#|\.\/)/i.test(clean) && !/^[\s\x00-\x1f]*javascript:/i.test(clean);
    }

    function buildCleanNode(node) {
      if (!node) return null;

      if (node.nodeType === Node.TEXT_NODE) {
        return document.createTextNode(node.nodeValue);
      }

      if (node.nodeType === Node.ELEMENT_NODE) {
        const tagName = node.nodeName.toLowerCase();

        if (dangerousTags.has(tagName)) {
          return null;
        }

        // In title mode, block elements are unwrapped with line-breaks (<br>)
        if (isTitle && ['p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'].includes(tagName)) {
          const frag = document.createDocumentFragment();
          for (let i = 0; i < node.childNodes.length; i++) {
            const childNode = buildCleanNode(node.childNodes[i]);
            if (childNode) frag.appendChild(childNode);
          }
          if (node.nextSibling) {
            frag.appendChild(document.createElement('br'));
          }
          return frag;
        }

        if (!allowedTags.has(tagName)) {
          const frag = document.createDocumentFragment();
          for (let i = 0; i < node.childNodes.length; i++) {
            const childNode = buildCleanNode(node.childNodes[i]);
            if (childNode) frag.appendChild(childNode);
          }
          return frag;
        }

        const cleanEl = document.createElement(tagName);

        if (tagName === 'a') {
          const href = node.getAttribute('href');
          const safeHref = href && isSafeUrl(href) ? href.trim() : '#';
          cleanEl.setAttribute('href', safeHref);
          cleanEl.setAttribute('target', '_blank');
          cleanEl.setAttribute('rel', 'noopener noreferrer');
          const title = node.getAttribute('title');
          if (title) cleanEl.setAttribute('title', title.trim());
        }

        if (tagName === 'td' || tagName === 'th') {
          const colspan = node.getAttribute('colspan');
          if (colspan && /^\d+$/.test(colspan)) cleanEl.setAttribute('colspan', colspan);
          const rowspan = node.getAttribute('rowspan');
          if (rowspan && /^\d+$/.test(rowspan)) cleanEl.setAttribute('rowspan', rowspan);
        }

        const className = node.getAttribute('class');
        if (className && /^[a-zA-Z0-9_\-\s]+$/.test(className)) {
          cleanEl.className = className.trim();
        }

        for (let i = 0; i < node.childNodes.length; i++) {
          const childNode = buildCleanNode(node.childNodes[i]);
          if (childNode) cleanEl.appendChild(childNode);
        }

        return cleanEl;
      }

      return null;
    }

    const fragment = document.createDocumentFragment();
    const sourceNodes = doc.body.childNodes;
    for (let i = 0; i < sourceNodes.length; i++) {
      const cleanChild = buildCleanNode(sourceNodes[i]);
      if (cleanChild) fragment.appendChild(cleanChild);
    }

    if (!fragment.hasChildNodes() && options.fallback) {
      targetEl.appendChild(document.createTextNode(options.fallback));
    } else {
      targetEl.appendChild(fragment);
    }
  }

  // Expose globally for all admin modules
  window.renderSafeHtml = renderSafeHtml;

  // Helper to serialize Quill content respecting title mode
  function getCleanOutputForQuill(quill, isTitleMode) {
    if (isTitleMode) {
      const raw = parseQuillToHtml(quill);
      const doc = new DOMParser().parseFromString(raw, 'text/html');
      const lines = [];
      doc.body.querySelectorAll('p, div, h1, h2, h3, h4').forEach((el) => {
        const t = el.innerHTML.trim();
        if (t && t !== '<br>') lines.push(t);
      });
      if (lines.length > 0) return lines.join('<br>');
      return doc.body.innerHTML.trim();
    }
    return parseQuillToHtml(quill);
  }

  function getCleanOutput() {
    return getCleanOutputForQuill(quill, isTitleMode);
  }

    // 2. Load and parse initial content from database
    const initialRaw = targetInput.value || '';
    if (initialRaw.trim()) {
      let parsedInitial = initialRaw;
      if (isTitleMode) {
        parsedInitial = '<p>' + initialRaw.replace(/<br\s*\/?>/gi, '</p><p>') + '</p>';
      } else {
        parsedInitial = parseMarkdownAndHtml(initialRaw);
      }
      quill.clipboard.dangerouslyPasteHTML(0, parsedInitial || initialRaw);
    }

    // 3. Paste Interceptor: automatically parses markdown and HTML on paste
    quill.clipboard.addMatcher(Node.TEXT_NODE, (node, delta) => {
      const text = node.data;
      if (text && (/(\*\*|__|\*|_|~~|\[.*?\]\(.*?\)|<[a-z0-9]+>|^#|^>|^-|\d+\.)/im.test(text))) {
        const parsed = parseMarkdownAndHtml(text);
        if (parsed !== text) {
          const wrapper = document.createElement('div');
          wrapper.innerHTML = parsed;
          return quill.clipboard.convert({ html: wrapper.innerHTML });
        }
      }
      return delta;
    });

    // 4. Live Typing Markdown Shortcuts (Keystroke triggers)
    quill.on('text-change', (delta, oldDelta, source) => {
      if (source !== 'user') {
        targetInput.value = getCleanOutput();
        return;
      }

      // Check last inserted character
      const range = quill.getSelection();
      if (!range) return;

      const [line, offset] = quill.getLine(range.index);
      if (!line) return;

      const lineText = line.domNode.textContent || '';

      // Block-level shortcuts on space (e.g. "# ", "## ", "### ", "> ", "- ", "* ", "1. ")
      if (offset <= 5) {
        if (lineText.startsWith('### ')) {
          const lineStart = range.index - offset;
          quill.deleteText(lineStart, 4, 'user');
          quill.formatLine(lineStart, 0, 'header', 4, 'user');
          return;
        }
        if (lineText.startsWith('## ')) {
          const lineStart = range.index - offset;
          quill.deleteText(lineStart, 3, 'user');
          quill.formatLine(lineStart, 0, 'header', 3, 'user');
          return;
        }
        if (lineText.startsWith('# ')) {
          const lineStart = range.index - offset;
          quill.deleteText(lineStart, 2, 'user');
          quill.formatLine(lineStart, 0, 'header', 2, 'user');
          return;
        }
        if (lineText.startsWith('> ')) {
          const lineStart = range.index - offset;
          quill.deleteText(lineStart, 2, 'user');
          quill.formatLine(lineStart, 0, 'blockquote', true, 'user');
          return;
        }
        if (lineText.startsWith('- ') || lineText.startsWith('* ')) {
          const lineStart = range.index - offset;
          quill.deleteText(lineStart, 2, 'user');
          quill.formatLine(lineStart, 0, 'list', 'bullet', 'user');
          return;
        }
        if (/^\d+\.\s/.test(lineText)) {
          const match = lineText.match(/^\d+\.\s/);
          const lineStart = range.index - offset;
          quill.deleteText(lineStart, match[0].length, 'user');
          quill.formatLine(lineStart, 0, 'list', 'ordered', 'user');
          return;
        }
      }

      // Inline markdown shortcuts on typing closing tags (**bold**, *italic*, ~~strike~~)
      if (range.index >= 4) {
        const textBeforeCursor = quill.getText(Math.max(0, range.index - 50), Math.min(50, range.index));
        
        // Match **word**
        const boldMatch = textBeforeCursor.match(/\*\*([^\*\s][^\*]*?)\*\*$/);
        if (boldMatch) {
          const matchLen = boldMatch[0].length;
          const innerText = boldMatch[1];
          const matchStart = range.index - matchLen;
          quill.deleteText(matchStart, matchLen, 'user');
          quill.insertText(matchStart, innerText, 'user');
          quill.formatText(matchStart, innerText.length, 'bold', true, 'user');
          quill.setSelection(matchStart + innerText.length, 0, 'user');
          return;
        }

        // Match ~~word~~
        const strikeMatch = textBeforeCursor.match(/~~([^~\s][^~]*?)~~$/);
        if (strikeMatch) {
          const matchLen = strikeMatch[0].length;
          const innerText = strikeMatch[1];
          const matchStart = range.index - matchLen;
          quill.deleteText(matchStart, matchLen, 'user');
          quill.insertText(matchStart, innerText, 'user');
          quill.formatText(matchStart, innerText.length, 'strike', true, 'user');
          quill.setSelection(matchStart + innerText.length, 0, 'user');
          return;
        }
      }

      targetInput.value = getCleanOutput();
      updateLinkedPreview();
    });

    // 5. Preview Target Synchronization (e.g. data-preview-target="previewIntro")
    const previewTargetId = containerEl.dataset.previewTarget;
    const previewTargetEl = previewTargetId ? document.getElementById(previewTargetId) : null;

    function updateLinkedPreview() {
      if (previewTargetEl) {
        renderSafeHtml(previewTargetEl, getCleanOutput(), { isTitle: isTitleMode });
      }
    }

    updateLinkedPreview();

    // 6. Form Submission Synchronization
    const formEl = targetInput.closest('form');
    if (formEl) {
      formEl.addEventListener('submit', (e) => {
        const cleanVal = getCleanOutput();
        targetInput.value = cleanVal;
        updateLinkedPreview();

        if (targetInput.hasAttribute('required') && !cleanVal.trim()) {
          e.preventDefault();
          quill.focus();
          if (typeof window.showToast === 'function') {
            window.showToast('กรุณากรอกข้อมูลในช่องเนื้อหา', 'error');
          } else {
            alert('กรุณากรอกข้อมูลในช่องเนื้อหา');
          }
        }
      });
    }

    // Expose utilities on container
    containerEl.__quillInstance = quill;
    containerEl.__parseMarkdownAndHtml = parseMarkdownAndHtml;
  }

  // Auto-init on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', () => {
    ['quill-news-editor', 'quill-hero-editor', 'quill-hero-title-editor'].forEach((id) => {
      const el = document.getElementById(id);
      if (el) initQuillInstance(el);
    });

    document.querySelectorAll('[data-quill-editor]').forEach(initQuillInstance);
  });

  window.DBTQuillParser = {
    parseInlineMarkdown,
    parseMarkdownAndHtml,
    parseQuillToHtml,
    initQuillInstance,
    renderSafe: renderSafeHtml
  };
})();
