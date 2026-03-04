$ find /var/log/db -name "db-*.log" -mtime +0 -mtime -30 -exec grep -li "ERROR\|WARN" {} \; | \
    grep -v "DEBUG" | \
    while read file; do 
        if [ $(stat -c%s "$file") -gt 1048576 ]; then 
            dest_dir="/archive/db/$(date +%Y-%m)"
            mkdir -p "$dest_dir"
            gzip -c "$file" > "$dest_dir/$(basename "$file").gz"
            touch -r "$file" "$dest_dir/$(basename "$file").gz"
        fi
    done

$ find /etc/app \( -name "*.conf" -o -name "*.yml" -o -name "*.json" \) -mtime -3 -type f | \
    while read file; do 
        if head -n 50 "$file" | grep -q "# DEPRECATED\|TODO:"; then 
            continue 
        fi 
        echo "$file" 
    done > /tmp/sync_list.txt
		for server in server{1,2,3}; do
				rsync -avz --bwlimit=2048 --progress --files-from=/tmp/sync_list.txt \
						-e 'ssh -p 2222' /etc/app/ \
						${server}:/etc/app-backup/ 2>&1 | tee "sync_${server}.log"
		done

$ find /etc/app/configs \( -name "*.conf" -o -name "*.yaml" \) -mtime -2 -type f -exec grep -L "# DEPRECATED\|# TEST" {} \; | \
	rsync -avz --bwlimit=5120 --progress --files-from=- -e "ssh -p 2222" /etc/app/configs/ user@server{1,2,3}:/backup/configs/

$ find /var/log/myapp -name "*.log" -mtime -1 -size +100k -size -10M -exec egrep -li "ERROR\|FATAL" {} \; | \
    grep -v -E "DEBUG\|TEST" | \
    while read file; do
        dest_dir="/archive/logs/$(date +%Y-%m-%d)"
        mkdir -p "$dest_dir"
        gzip -c "$file" > "$dest_dir/$(basename "$file").gz"
        touch -r "$file" "$dest_dir/$(basename "$file").gz"
    done

$ find /etc/app/configs \( -name "*.conf" -o -name "*.yaml" \) -mtime -7 -type f -exec grep -L "# DEPRECATED\|# TEMPORARY" {} \; | \
    rsync -avz --progress --files-from=- /etc/app/configs/ user@server{1,2,3}:/backup/configs/

$ rsync -avz --info=progress2 -e ssh --files-from=<( find . \( -name "*.js" -o -name "*.css" \) -size +1k -mtime -2-type f ) \
	user@remote:/backup/web/

$ find . -type f -exec du -h {} + | sort -rh | head -10

$ ps aux --sort=-%mem | head -6

$ grep -oE "(INFO|ERROR|WARN)" logfile.txt | sort | uniq -c 

$ grep -o "user=[^ ]*" logfile.txt | cut -d= -f2 

$ tr '[:lower:]' '[:upper:]' < logfile.txt

# Skip header and print second column
$ awk -F, 'NR>1 {print $2}' sales.csv 
# Calculate average salary (skip header)
$ awk -F, 'NR>1 {sum+=$3; count++} END{print "Average:", sum/count}' sales.csv 
# Group by department and calculate total salary per department
$ awk -F, 'NR>1 {dept[$2]+=$3} END{for(d in dept) print d, dept[d]}' sales.csv
# Show employees with salary > 80000
$ awk -F, '$3>80000 {print $1, $3}' sales.csv 
# Format output as tab-separated (TSV) instead of CSV
$ awk 'BEGIN{FS=","; OFS="\t"} {$1=$1; print}' sales.csv

# Find lines common to both files (intersection) 
$ comm -12 <(sort file1.txt) <(sort file2.txt)
# Find lines unique to file1.txt (not in file2.txt) 
$ comm -23 <(sort file1.txt) <(sort file2.txt) 
# Merge two files, remove duplicates, sort alphabetically 
$ sort -u file1.txt file2.txt 
# Show differences between two files (diff format) 
diff file1.txt file2.txt 
# Split file into multiple files of 2 lines each 
split -l 2 file1.txt split_ 
# Join two files side by side (paste) 
paste file1.txt file2.txt 
# Remove all blank lines from a file 
grep -v "^$" file.txt
# Find all files with multiple blank lines
$ find . -maxdepth 1 -type f -exec sh -c 'awk "/^[[:space:]]*$/ {blank++} !/^[[:space:]]*$/ {if(blank>=2) exit 0; blank=0} END{exit !(blank>=2)}" "$1" && echo "$1 has 2+ consecutive blanks"' _ {} \;
# Schedule one-time system reboot at 3 AM tomorrow 
echo "shutdown -r now" | at 3am tomorrow 
# Kill all processes for user 'johndoe'
pkill -KILL -u johndoe
# Basic firewall: Allow SSH, HTTP, HTTPS; drop everything else 
iptables -A INPUT -p tcp --dport 22 -j ACCEPT 
iptables -A INPUT -p tcp --dport 80 -j ACCEPT 
iptables -A INPUT -p tcp --dport 443 -j ACCEPT 
iptables -P INPUT DROP 
# Rate limiting: Max 3 connections per minute per IP to SSH 
iptables -A INPUT -p tcp --dport 22 -m limit --limit 3/min --limit-burst 5 -j ACCEPT 
# Monitor real-time traffic by protocol 
iftop -i eth0 
# Traffic shaping: Limit bandwidth for specific IP 
tc qdisc add dev eth0 root handle 1: htb 
# Configure fail2ban for SSH protection 
cat > /etc/fail2ban/jail.local << EOF
[sshd]
enabled = true
maxretry = 3
bantime = 3600
EOF
# Monitor firewall logs in real-time 
tail -f /var/log/syslog | grep iptables
# Capture HTTP traffic from specific IP, save to file 
tcpdump -i eth0 -w capture.pcap port 80 and host 192.168.1.100 
# Capture with size limit (rotating files, 10 files of 10MB each) 
tcpdump -i eth0 -C 10 -W 10 -w capture port 80 
# Analyze captured file: show HTTP requests 
tshark -r capture.pcap -Y http.request | grep -E "(GET|POST|Host:)" 
# Extract files from HTTP traffic in pcap 
tshark -r capture.pcap --export-objects http,./extracted
# Stealth host discovery (no port scan, just find live hosts) 
nmap -sn 192.168.1.0/24 
# Comprehensive scan of target with version detection 
nmap -sV -sC -O -p- 192.168.1.100 
# OS detection and traceroute 
nmap -O --osscan-guess 192.168.1.100 
# Save results in multiple formats 
nmap -oA scan_report 192.168.1.100
# Web vulnerability scanning 
nikto -h http://192.168.1.100 