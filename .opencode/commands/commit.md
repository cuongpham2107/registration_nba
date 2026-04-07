---
description: Create a git commit with conventional commit message
agent: git-helper
model: alibaba-cn/qwen3.6-plus
---
Analyze the current changes and create a commit with a proper conventional commit message.

Current status:
!`git status`

Diff:
!`git diff`

1. Analyze the changes
2. Generate a conventional commit message (feat, fix, docs, style, refactor, test, chore)
3. Stage relevant files
4. Create the commit
5. Show the commit summary

Follow the format: `type(scope): description`
