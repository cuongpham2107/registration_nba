---
description: Review recent code changes
agent: review
model: alibaba-cn/qwen3.6-plus
---
Review the recent changes in the codebase.

Recent commits:
!`git log --oneline -10`

Current changes:
!`git diff HEAD~5`

Review the code focusing on:
1. Correctness and logic
2. Laravel/Filament/Livewire conventions
3. Security issues
4. Performance concerns (N+1 queries, etc.)
5. Code quality and maintainability

Provide feedback in Vietnamese.
