import json, sqlite3, os, datetime
LOCAL = os.path.expanduser("~/.config/Code/User/workspaceStorage")

added_total = 0
for wsid in os.listdir(LOCAL):
    if len(wsid) != 32: continue
    
    chat_dir = f"{LOCAL}/{wsid}/chatSessions"
    db_path = f"{LOCAL}/{wsid}/state.vscdb"
    
    if not os.path.exists(chat_dir) or not os.path.exists(db_path):
        continue

    try:
        conn = sqlite3.connect(db_path)
        cur = conn.cursor()
        cur.execute("SELECT value FROM ItemTable WHERE key=\"chat.ChatSessionStore.index\"")
        row = cur.fetchone()
        
        if row:
            index = json.loads(row[0])
            added_count = 0
            
            for fname in os.listdir(chat_dir):
                if not fname.endswith(".jsonl") or fname.endswith(".bak"): continue
                sid = fname.replace(".jsonl", "")
                
                if sid not in index:
                    try:
                        fpath = os.path.join(chat_dir, fname)
                        with open(fpath, "r", encoding="utf-8") as f:
                            data = json.loads(f.readline())
                            
                        reqs = len(data.get("v", {}).get("requests", []))
                        if reqs > 0:
                            title = data.get("v", {}).get("customTitle", data.get("v", {}).get("title", "Untitled"))
                            index[sid] = {
                                "title": title,
                                "creationDate": data.get("v", {}).get("creationDate", datetime.datetime.now().timestamp() * 1000),
                                "workspaceId": wsid,
                                "isEmpty": False
                            }
                            added_count += 1
                    except Exception as ex:
                        pass
            
            if added_count > 0:
                cur.execute("UPDATE ItemTable SET value=? WHERE key=\"chat.ChatSessionStore.index\"", (json.dumps(index),))
                conn.commit()
                print(f"[{wsid[:8]}...] Added {added_count} sessions to index.")
                added_total += added_count
        
        conn.close()
    except Exception as e:
        pass

print(f"Finished updating all workspace indexes. Total added: {added_total}")
