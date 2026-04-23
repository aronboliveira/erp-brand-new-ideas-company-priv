#!/usr/bin/env python3
"""
Comprehensive Route Smoke Test Script
Tests all GET/HEAD routes with different curl flag combinations
Saves results to _inc/laravel/utils/.llms/smoke_test_results.json
"""

import subprocess
import json
import re
import os
from pathlib import Path
from datetime import datetime
from collections import defaultdict
import sys

# Configuration
BASE_URL = os.environ.get("SMOKE_TEST_URL", "http://127.0.0.1:8888")
SCRIPT_DIR = Path(__file__).resolve().parent
LLMS_DIR = SCRIPT_DIR.parent
TEST_SCENARIOS = [
    {
        "name": "basic",
        "flags": ["-s", "-o", "/dev/null", "-w", "%{http_code}"],
        "description": "Basic status code check"
    },
    {
        "name": "with_redirects",
        "flags": ["-sL", "-o", "/dev/null", "-w", "%{http_code}|%{redirect_url}"],
        "description": "Follow redirects and track final destination"
    },
    {
        "name": "timing",
        "flags": ["-s", "-o", "/dev/null", "-w", "%{http_code}|%{time_total}|%{time_starttransfer}"],
        "description": "Performance timing"
    },
    {
        "name": "headers",
        "flags": ["-sI", "-o", "/dev/null", "-w", "%{http_code}|%{content_type}"],
        "description": "HEAD request with content type"
    }
]

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
        # Skip headers, debugbar, ignition, sanctum
        if any(skip in line for skip in ['debugbar', '_ignition', 'sanctum/csrf', 'Method', '─']):
            continue
        
        # Match GET|HEAD lines - format: "  GET|HEAD        /uri name › Controller..."
        if 'GET|HEAD' in line or re.match(r'^\s+GET\s', line):
            # Extract URI - it's the second column after method
            match = re.search(r'(GET\|HEAD|GET)\s+(\S+)', line)
            if match:
                uri = match.group(2).strip()
                
                # Extract route name if present
                name_match = re.search(r'(\S+)\s+›', line)
                name = name_match.group(1) if name_match else ''
                
                # Skip routes with parameters for basic smoke test
                # Also skip .well-known and other special routes
                if '{' not in uri and uri and not uri.startswith('.well-known'):
                    routes.append({
                        'method': 'GET|HEAD',
                        'uri': uri.strip(),
                        'name': name.strip()
                    })
    
    print(f"Found {len(routes)} testable GET routes (excluding parameterized routes)")
    return routes

def test_route(route, scenario):
    """Test a single route with given scenario"""
    url = f"{BASE_URL}/{route['uri']}"
    cmd = ["curl"] + scenario["flags"] + [url]
    
    try:
        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            timeout=10
        )
        return {
            "success": True,
            "output": result.stdout.strip(),
            "error": result.stderr.strip() if result.returncode != 0 else None
        }
    except subprocess.TimeoutExpired:
        return {
            "success": False,
            "output": None,
            "error": "Timeout after 10 seconds"
        }
    except Exception as e:
        return {
            "success": False,
            "output": None,
            "error": str(e)
        }

def run_smoke_tests():
    """Run all smoke tests"""
    routes = get_routes()
    
    if not routes:
        print("No routes found. Is the Laravel app running?")
        return
    
    results = {
        "timestamp": datetime.now().isoformat(),
        "total_routes": len(routes),
        "scenarios": {},
        "routes": {}
    }
    
    for scenario in TEST_SCENARIOS:
        print(f"\n=== Testing scenario: {scenario['name']} ({scenario['description']}) ===")
        scenario_results = {
            "description": scenario["description"],
            "status_codes": defaultdict(int),
            "failed": [],
            "slow": []  # > 2 seconds
        }
        
        for i, route in enumerate(routes):
            if (i + 1) % 50 == 0:
                print(f"Progress: {i + 1}/{len(routes)}")
            
            test_result = test_route(route, scenario)
            
            # Store per-route results
            if route['uri'] not in results['routes']:
                results['routes'][route['uri']] = {
                    'name': route['name'],
                    'tests': {}
                }
            
            results['routes'][route['uri']]['tests'][scenario['name']] = test_result
            
            # Parse status code from output
            if test_result['success'] and test_result['output']:
                parts = test_result['output'].split('|')
                status_code = parts[0]
                scenario_results['status_codes'][status_code] += 1
                
                # Track slow routes (timing scenario)
                if scenario['name'] == 'timing' and len(parts) > 1:
                    try:
                        total_time = float(parts[1])
                        if total_time > 2.0:
                            scenario_results['slow'].append({
                                'uri': route['uri'],
                                'time': total_time
                            })
                    except ValueError:
                        pass
                
                # Track failures
                if status_code in ['500', '404', '403', '401']:
                    scenario_results['failed'].append({
                        'uri': route['uri'],
                        'status': status_code,
                        'name': route['name']
                    })
            else:
                scenario_results['status_codes']['ERROR'] += 1
                scenario_results['failed'].append({
                    'uri': route['uri'],
                    'status': 'ERROR',
                    'error': test_result.get('error', 'Unknown error')
                })
        
        results['scenarios'][scenario['name']] = scenario_results
        
        # Print summary
        print(f"\nScenario '{scenario['name']}' results:")
        print(f"  Status codes: {dict(scenario_results['status_codes'])}")
        print(f"  Failed routes: {len(scenario_results['failed'])}")
        if scenario_results['slow']:
            print(f"  Slow routes (>2s): {len(scenario_results['slow'])}")
    
    return results

def save_results(results):
    """Save results to persistent storage"""
    output_path = LLMS_DIR / "smoke_test_results.json"
    
    with open(output_path, 'w') as f:
        json.dump(results, f, indent=2)
    
    print(f"\n✓ Full results saved to: {output_path}")
    
    # Also create a human-readable summary
    summary_path = LLMS_DIR / "smoke_test_summary.txt"
    
    with open(summary_path, 'w') as f:
        f.write(f"Route Smoke Test Summary\n")
        f.write(f"Generated: {results['timestamp']}\n")
        f.write(f"Total Routes Tested: {results['total_routes']}\n")
        f.write(f"\n{'='*80}\n\n")
        
        for scenario_name, scenario_data in results['scenarios'].items():
            f.write(f"Scenario: {scenario_name}\n")
            f.write(f"Description: {scenario_data['description']}\n")
            f.write(f"\nStatus Code Distribution:\n")
            for code, count in sorted(scenario_data['status_codes'].items()):
                f.write(f"  {code}: {count}\n")
            
            if scenario_data['failed']:
                f.write(f"\nFailed Routes ({len(scenario_data['failed'])}):\n")
                for failed in scenario_data['failed'][:20]:  # Show first 20
                    f.write(f"  [{failed.get('status', 'ERROR')}] /{failed['uri']}")
                    if failed.get('name'):
                        f.write(f" ({failed['name']})")
                    if failed.get('error'):
                        f.write(f" - {failed['error']}")
                    f.write('\n')
                
                if len(scenario_data['failed']) > 20:
                    f.write(f"  ... and {len(scenario_data['failed']) - 20} more\n")
            
            if scenario_data.get('slow'):
                f.write(f"\nSlow Routes (>{2.0}s, {len(scenario_data['slow'])}):\n")
                for slow in sorted(scenario_data['slow'], key=lambda x: x['time'], reverse=True)[:10]:
                    f.write(f"  {slow['time']:.2f}s - /{slow['uri']}\n")
            
            f.write(f"\n{'-'*80}\n\n")
    
    print(f"✓ Summary saved to: {summary_path}")

if __name__ == "__main__":
    print("Starting comprehensive route smoke tests...")
    print(f"Base URL: {BASE_URL}")
    print(f"Test scenarios: {len(TEST_SCENARIOS)}")
    
    results = run_smoke_tests()
    
    if results:
        save_results(results)
        
        # Print quick summary
        print("\n" + "="*80)
        print("QUICK SUMMARY")
        print("="*80)
        
        for scenario_name, scenario_data in results['scenarios'].items():
            codes = scenario_data['status_codes']
            print(f"\n{scenario_name.upper()}:")
            print(f"  2xx: {sum(v for k, v in codes.items() if k.startswith('2'))}")
            print(f"  3xx: {sum(v for k, v in codes.items() if k.startswith('3'))}")
            print(f"  4xx: {sum(v for k, v in codes.items() if k.startswith('4'))}")
            print(f"  5xx: {sum(v for k, v in codes.items() if k.startswith('5'))}")
            print(f"  ERR: {codes.get('ERROR', 0)}")
