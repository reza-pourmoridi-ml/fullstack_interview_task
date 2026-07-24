Cursor-based Vue/Pinia frontend module generated from chat output.

Important:
- This ZIP contains only the generated frontend module, not the original project frontend.
- The original archive provided in chat did not include frontend source files.
- You should merge/adapt these files into your real Vue 3 + Vite project.
- Verify backend response shape, especially:
  - meta.next_cursor
  - meta.total
  - payment fields
  - supported query params: user_id, start, end, per_page, cursor_created_at, cursor_id
