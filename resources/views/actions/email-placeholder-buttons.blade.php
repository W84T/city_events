<div class="flex space-x-2 mb-4">
    <button type="button" onclick="insertPlaceholder('{first_name}')" class="px-4 py-2 bg-blue-500 text-white rounded">First Name</button>
    <button type="button" onclick="insertPlaceholder('{last_name}')" class="px-4 py-2 bg-blue-500 text-white rounded">Last Name</button>
    <button type="button" onclick="insertPlaceholder('{title}')" class="px-4 py-2 bg-blue-500 text-white rounded">Title</button>
    <button type="button" onclick="insertPlaceholder('{job_title}')" class="px-4 py-2 bg-blue-500 text-white rounded">Job Title</button>
    <button type="button" onclick="insertPlaceholder('{mobile_number}')" class="px-4 py-2 bg-blue-500 text-white rounded">Mobile Number</button>
</div>

<script>
    function insertPlaceholder(placeholder) {
        // Find the RichEditor's content area
        const editor = document.querySelector('[data-field="email_body"] .ql-editor');
        if (editor) {
            // Insert the placeholder at the cursor position
            const selection = window.getSelection();
            const range = selection.getRangeAt(0);
            range.deleteContents();
            const textNode = document.createTextNode(placeholder);
            range.insertNode(textNode);

            // Move the cursor to the end of the inserted placeholder
            range.setStartAfter(textNode);
            range.setEndAfter(textNode);
            selection.removeAllRanges();
            selection.addRange(range);

            // Focus the editor
            editor.focus();
        }
    }
</script>
