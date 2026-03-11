import json, os, sqlite3

LOCAL = os.path.expanduser("~/.config/Code/User/workspaceStorage")
ts_agent_id = "ed786840-5067-47ae-b1ad-5f73ba1b7f26"

for wsid in os.listdir(LOCAL):
    if len(wsid) != 32: continue
    chat_dir = f"{LOCAL}/{wsid}/chatSessions"
    fpath = f"{chat_dir}/{ts_agent_id}.jsonl"
    
    if os.path.exists(fpath):
        try:
            with open(fpath) as f:
                first_line = json.loads(f.readline())
            title = first_line.get('v', {}).get('customTitle', first_line.get('v', {}).get('title', 'Unknown'))
            print(f"Workspace: {wsid}")
            print(f"File Title: {title}")
            
            db_path = f"{LOCAL}/{wsid}/state.vscdb"
            if os.path.exists(db_path):
                conn = sqlite3.connect(db_path)
                cur = conn.cursor()
                cur.execute("SELECT value FROM ItemTable WHERE key='chat.ChatSessionStore.index'")
                row = cur.fetchone()
                if row:
                    index = json.loads(row[0])
                    if ts_agent_id in index:
                        print(f"Index DB DB Title: {index[ts_agent_id].get('title', 'Unknown title')}")
                        print(f"Index DB isEmpty: {index[ts_agent_id].get('isEmpty')}")
                        print(f"Index DB workspace: {index[ts_agent_id].get('workspaceId')}")
                    else:
                        print("NOT FOUND IN DB INDEX")
                conn.close()
        except Exception as e:
            print(f"Error: {e}")
