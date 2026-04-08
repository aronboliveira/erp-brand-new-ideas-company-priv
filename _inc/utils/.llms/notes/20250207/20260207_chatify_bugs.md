# Chatify Vendor Bugs

## Status: Wrapped (not fixed upstream)

## Date: 2026-02-07

### Problem

1. `MessagesController::download()` expects 1 argument but vendor route passes 0
2. This causes 500 errors at `/chats/downloads/{file-name}`
3. May also flood frontend console if JS tries to call this endpoint

### Current Fix

- Route override added in routes/web.php to intercept the download route
- Try/catch wrapper passes the file name correctly
- Falls back to 404 if download still fails

### TODO

- Check if a newer Chatify version fixes this
- Consider forking the package if needed
- Review all Chatify JS calls for error handling
