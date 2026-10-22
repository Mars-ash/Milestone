import json
from datetime import datetime, timedelta

def to_date(val):
    if not val:
        return None
    try:
        serial = float(val)
        if serial < 1000:
            return None
        dt = datetime(1899, 12, 30) + timedelta(days=serial)
        return dt.strftime('%Y-%m-%d')
    except (ValueError, TypeError):
        return str(val)

with open('tools/projects_dump.json', 'r', encoding='utf-8') as f:
    projects = json.load(f)

with open('tools/progresses_dump.json', 'r', encoding='utf-8') as f:
    progresses = json.load(f)

print("=== PROJECT INTEGRITY ===")
jo_counts = {}
for p in projects:
    jo = p.get('JO No')
    jo_counts[jo] = jo_counts.get(jo, 0) + 1
    if not p.get('JO No') or not p.get('Part No') or not p.get('Part Name'):
        print(f"Project missing required identity: {p}")

dupe_jos = {jo: count for jo, count in jo_counts.items() if count > 1}
print(f"Duplicate JOs in projects: {dupe_jos}")

print("\n=== PROGRESS INSPECTION ===")
open_count = 0
closed_count = 0
with_problems = 0
with_ca = 0
with_na = 0
with_feedback = 0
with_docs = 0
null_dates = 0
null_activities = 0

for p in progresses:
    is_close = p.get('Progress Close')
    if is_close == '1':
        closed_count += 1
    else:
        open_count += 1

    if not p.get('Date'):
        null_dates += 1
    if not p.get('Activity'):
        null_activities += 1

    if p.get('Problem'):
        with_problems += 1
    if p.get('Corrective Action'):
        with_ca += 1
    if p.get('Next Action'):
        with_na += 1
    if p.get('Feedback Customer'):
        with_feedback += 1
    
    docs = [p.get(f'Doc{i}') for i in range(1, 6) if p.get(f'Doc{i}')]
    if docs:
        with_docs += 1

print(f"Open Progresses: {open_count}")
print(f"Closed Progresses: {closed_count}")
print(f"Progress with Null Date: {null_dates}")
print(f"Progress with Null Activity: {null_activities}")
print(f"Progress with Problems: {with_problems}")
print(f"Progress with Corrective Actions: {with_ca}")
print(f"Progress with Next Actions: {with_na}")
print(f"Progress with Feedback: {with_feedback}")
print(f"Progress with Legacy Docs: {with_docs}")

# Sample dates converted
print("\nSample Project Dates converted:")
for p in projects[:3]:
    print(f"JO: {p.get('JO No')}, Part: {p.get('Part Name')}, T0 Target: {to_date(p.get('T0 Target'))}, Plan Finish: {to_date(p.get('Plan Finish'))}")

print("\nSample Progress Dates converted:")
for p in progresses[:3]:
    print(f"ID: {p.get('ID Progress')}, Date: {to_date(p.get('Date'))}, Activity: {p.get('Activity')}, Status: {'Closed' if p.get('Progress Close') == '1' else 'Open'}")
