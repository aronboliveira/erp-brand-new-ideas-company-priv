import json, sqlite3, os
wsid = "cf21ca66b97084da6cc9ddf543624480"
ts_id = "ed786840-5067-47ae-b1ad-5f73ba1b7f26"
db_path = os.path.expanduser(f"~/.config/Code/User/workspaceStorage/{wsid}/state.vscdb")
try:
    conn = sqlite3.connect(db_path)
    cur = conn.cursor()
    cur.execute("SELECT value FROM ItemTable WHERE key=\"chat.ChatSessionStore.index\"")
    row = cur.fetchone()
    if row:
        index = json.loads(row[0])
        print("Is TS-AGENT in DB?", ts_id in index)
        if ts_id in index: print("Title in DB:", index[ts_id].get("title"))
except Exception as e:
    print(e)
