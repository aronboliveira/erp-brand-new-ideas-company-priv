#!/usr/bin/env python3
"""
Authenticated Route Smoke Test
Tests routes after logging in and using authentication cookies
"""

import subprocess
import json
import re
from datetime import datetime
from collections import defaultdict
import tempfile
import os

# Configuration
BASE_URL = os.environ.get("SMOKE_TEST_URL", "http://127.0.0.1:8888")
LOGIN_EMAIL = os.environ.get("SMOKE_TEST_EMAIL", "alexys87@example.org")
LOGIN_PASSWORD = os.environ.get("SMOKE_TEST_PASSWORD", "Admin@1234")

def get_routes():
    """Extract all GET/HEAD routes from Laravel"""
    print("Extracting routes from Laravel...")
    result = subprocess.run(
        ["php", "artisan", "route:list"],
        capture_output=True,
        text=True,
        cwd="/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/laravel"
    )
    
    routes = []
    for line in result.stdout.split('\n'):
        if any(skip in line for skip in ['debugbar', '_ignition', 'sanctum/csrf', 'Method', '─']):
            continue
        
        if 'GET|HEAD' in line or re.match(r'^\s+GET\s', line):
            match = re.search(r'(GET\|HEAD|GET)\s+(\S+)', line)
            if match:
                uri = match.group(2).strip()
                name_match = re.search(r'(\S+)\s+›', line)
                name = name_match.group(1) if name_match else ''
                
                if '{' not in uri and uri and not uri.startswith('.well-known'):
                    routes.append({
                        'method': 'GET|HEAD',
                        'uri': uri.strip(),
                        'name': name.strip()
                    })
    
    print(f"Found {len(routes)} testable GET routes")
    return routes

def login_and_get_cookies():
    """Login and extract session cookies"""
    print("\nAttempting to authenticate...")
    
    # Create temp file for cookies
    cookie_file = tempfile.NamedTemporaryFile(mode='w', delete=False, suffix='.txt')
    cookie_path = cookie_file.name
    cookie_file.close()
    
    # First, get CSRF token
    csrf_result = subprocess.run(
        ["curl", "-s", "-c", cookie_path, f"{BASE_URL}/login"],
        capture_output=True,
        text=True
    )
    
    # Extract CSRF token from response
    csrf_match = re.search(r'name="_token"\s+value="([^"]+)"', csrf_result.stdout)
    if not csrf_match:
        csrf_match = re.search(r'meta name="csrf-token" content="([^"]+)"', csrf_result.stdout)
    
    if not csrf_match:
        print("Warning: Could not extract CSRF token. Login may fail.")
        csrf_token = ""
    else:
        csrf_token = csrf_match.group(1)
        print(f"✓ Got CSRF token: {csrf_token[:20]}...")
    
    # Now login
    login_data = f"_token={csrf_token}&email={LOGIN_EMAIL}&password={LOGIN_PASSWORD}"
    login_result = subprocess.run(
        [
            "curl", "-s",
            "-b", cookie_path,
            "-c", cookie_path,
            "-X", "POST",
            "-H", "Content-Type: application/x-www-form-urlencoded",
            "-L",  # Follow redirects
            "-w", "%{http_code}",
            "-o", "/dev/null",
            "-d", login_data,
            f"{BASE_URL}/login"
        ],
        capture_output=True,
        text=True
    )
    
    status = login_result.stdout.strip()
    print(f"Login response: {status}")
    
    # Check if cookies were set
    if os.path.exists(cookie_path):
        with open(cookie_path, 'r') as f:
            cookies = f.read()
            if '_session' in cookies or 'XSRF-TOKEN' in cookies:
                print("✓ Authentication cookies obtained")
                return cookie_path
            else:
                print("Warning: No session cookie found")
    
    return cookie_path

def test_route_authenticated(route, cookie_path):
    """Test a route with authentication cookies"""
    url = f"{BASE_URL}/{route['uri']}"
    
    try:
        result = subprocess.run(
            [
                "curl", "-s",
                "-b", cookie_path,
                "-w", "%{http_code}|%{redirect_url}|%{time_total}",
                "-o", "/dev/null",
                "-L",  # Follow redirects
                url
            ],
            capture_output=True,
            text=True,
            timeout=10
        )
        
        output = result.stdout.strip()
        parts = output.split('|')
        
        return {
            "success": True,
            "status_code": parts[0] if len(parts) > 0 else "ERROR",
            "redirect_url": parts[1] if len(parts) > 1 else "",
            "time_total": float(parts[2]) if len(parts) > 2 and parts[2] else 0.0,
            "error": None
        }
    except subprocess.TimeoutExpired:
        return {
            "success": False,
            "status_code": "TIMEOUT",
            "error": "Timeout after 10 seconds"
        }
    except Exception as e:
        return {
            "success": False,
            "status_code": "ERROR",
            "error": str(e)
        }

def categorize_routes(routes, results):
    """Categorize routes by their response status"""
    categories = {
        "working": [],          # 200-299
        "redirects": [],        # 300-399
        "auth_required": [],    # 401, 403
        "not_found": [],        # 404
        "server_errors": [],    # 500-599
        "other": []
    }
    
    for route in routes:
        result = results.get(route['uri'], {})
        status = result.get('status_code', 'ERROR')
        
        entry = {
            'uri': route['uri'],
            'name': route['name'],
            'status': status
        }
        
        if status.startswith('2'):
            categories['working'].append(entry)
        elif status.startswith('3'):
            categories['redirects'].append(entry)
        elif status in ['401', '403']:
            categories['auth_required'].append(entry)
        elif status == '404':
            categories['not_found'].append(entry)
        elif status.startswith('5'):
            categories['server_errors'].append(entry)
        else:
            categories['other'].append(entry)
    
    return categories

def run_authenticated_tests():
    """Run smoke tests with authentication"""
    routes = get_routes()
    
    if not routes:
        print("No routes found.")
        return
    
    # Login and get cookies
    cookie_path = login_and_get_cookies()
    
    print(f"\n{'='*80}")
    print("Testing routes with authentication...")
    print(f"{'='*80}\n")
    
    results = {}
    status_counts = defaultdict(int)
    slow_routes = []
    
    for i, route in enumerate(routes):
        if (i + 1) % 50 == 0:
            print(f"Progress: {i + 1}/{len(routes)}")
        
        result = test_route_authenticated(route, cookie_path)
        results[route['uri']] = result
        
        status = result['status_code']
        status_counts[status] += 1
        
        # Track slow routes
        if result.get('time_total', 0) > 2.0:
            slow_routes.append({
                'uri': route['uri'],
                'time': result['time_total'],
                'status': status
            })
    
    # Cleanup cookie file
    try:
        os.unlink(cookie_path)
    except:
        pass
    
    # Categorize routes
    categories = categorize_routes(routes, results)
    
    # Save detailed results
    output_data = {
        "timestamp": datetime.now().isoformat(),
        "total_routes": len(routes),
        "authenticated": True,
        "status_counts": dict(status_counts),
        "categories": categories,
        "slow_routes": sorted(slow_routes, key=lambda x: x['time'], reverse=True),
        "results": results
    }
    
    json_path = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/utils/.llms/smoke_test_authenticated_results.json"
    with open(json_path, 'w') as f:
        json.dump(output_data, f, indent=2)
    
    print(f"\n✓ Detailed results saved to: {json_path}")
    
    # Create human-readable summary
    summary_path = "/home/aronboliveira/Desktop/programming/Prestech/erp/erpgo-fork/erp_prestech/_inc/utils/.llms/smoke_test_authenticated_summary.txt"
    
    with open(summary_path, 'w') as f:
        f.write("Authenticated Route Smoke Test Summary\n")
        f.write(f"Generated: {output_data['timestamp']}\n")
        f.write(f"Total Routes: {len(routes)}\n")
        f.write(f"\n{'='*80}\n\n")
        
        f.write("STATUS CODE DISTRIBUTION:\n")
        for code, count in sorted(status_counts.items()):
            f.write(f"  {code}: {count}\n")
        
        f.write(f"\n{'='*80}\n\n")
        
        for category_name, entries in categories.items():
            if entries:
                f.write(f"{category_name.upper().replace('_', ' ')} ({len(entries)}):\n")
                for entry in entries[:30]:  # Show first 30
                    f.write(f"  [{entry['status']}] /{entry['uri']}")
                    if entry['name']:
                        f.write(f" ({entry['name']})")
                    f.write('\n')
                
                if len(entries) > 30:
                    f.write(f"  ... and {len(entries) - 30} more\n")
                f.write('\n')
        
        if slow_routes:
            f.write(f"SLOW ROUTES (>2s, {len(slow_routes)}):\n")
            for slow in slow_routes[:20]:
                f.write(f"  {slow['time']:.2f}s [{slow['status']}] /{slow['uri']}\n")
            f.write('\n')
    
    print(f"✓ Summary saved to: {summary_path}")
    
    # Print quick summary
    print(f"\n{'='*80}")
    print("SUMMARY")
    print(f"{'='*80}")
    print(f"Working (2xx):        {len(categories['working'])}")
    print(f"Redirects (3xx):      {len(categories['redirects'])}")
    print(f"Auth Required (401/403): {len(categories['auth_required'])}")
    print(f"Not Found (404):      {len(categories['not_found'])}")
    print(f"Server Errors (5xx):  {len(categories['server_errors'])}")
    print(f"Slow Routes (>2s):    {len(slow_routes)}")
    print(f"{'='*80}")
    
    # Routes needing work
    needs_work = len(categories['server_errors']) + len(categories['not_found'])
    print(f"\n⚠️  Routes needing work: {needs_work}")

if __name__ == "__main__":
    print("Authenticated Route Smoke Test")
    print(f"Base URL: {BASE_URL}")
    print(f"Login Email: {LOGIN_EMAIL}")
    run_authenticated_tests()
