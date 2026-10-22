import zipfile
import xml.etree.ElementTree as ET
import json
from datetime import datetime, timedelta

def excel_date_to_str(val):
    if not val:
        return None
    try:
        # Excel date serial (days since 1899-12-30)
        serial = float(val)
        if serial < 1000: # not a date serial
            return val
        dt = datetime(1899, 12, 30) + timedelta(days=serial)
        return dt.strftime('%Y-%m-%d')
    except (ValueError, TypeError):
        return val

def parse_xlsx(file_path):
    with zipfile.ZipFile(file_path) as z:
        shared_strings = []
        if 'xl/sharedStrings.xml' in z.namelist():
            tree = ET.fromstring(z.read('xl/sharedStrings.xml'))
            ns = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
            for si in tree.findall('ns:si', ns):
                t = si.find('ns:t', ns)
                if t is not None and t.text:
                    shared_strings.append(t.text)
                else:
                    parts = [r.find('ns:t', ns).text for r in si.findall('ns:r', ns) if r.find('ns:t', ns) is not None and r.find('ns:t', ns).text]
                    shared_strings.append(''.join(parts))

        tree = ET.fromstring(z.read('xl/worksheets/sheet1.xml'))
        ns = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
        
        rows = []
        for row_elem in tree.findall('.//ns:row', ns):
            row_data = {}
            for c in row_elem.findall('ns:c', ns):
                cell_ref = c.attrib.get('r', '')
                col_letter = ''.join([ch for ch in cell_ref if ch.isalpha()])
                cell_type = c.attrib.get('t', '')
                v = c.find('ns:v', ns)
                val = v.text if v is not None else None
                
                if cell_type == 's' and val is not None:
                    idx = int(val)
                    val = shared_strings[idx] if idx < len(shared_strings) else val
                elif cell_type == 'inlineStr':
                    is_t = c.find('.//ns:t', ns)
                    if is_t is not None:
                        val = is_t.text
                
                row_data[col_letter] = val
            rows.append(row_data)
        
        return rows

def analyze():
    # 1. Projects
    proj_rows = parse_xlsx('import/Table - Project List Master.xlsx')
    header_proj = proj_rows[0]
    col_map_proj = {col: header_proj[col] for col in sorted(header_proj.keys())}
    
    projects_data = []
    for r in proj_rows[1:]:
        mapped = {col_map_proj[col]: r.get(col) for col in col_map_proj}
        projects_data.append(mapped)

    # 2. Progresses
    prog_rows = parse_xlsx('import/Table - Progress New model Master.xlsx')
    header_prog = prog_rows[0]
    col_map_prog = {col: header_prog[col] for col in sorted(header_prog.keys())}
    
    progresses_data = []
    for r in prog_rows[1:]:
        mapped = {col_map_prog[col]: r.get(col) for col in col_map_prog}
        progresses_data.append(mapped)

    # Save to json in tools/
    with open('tools/projects_dump.json', 'w', encoding='utf-8') as f:
        json.dump(projects_data, f, indent=2, ensure_ascii=False)

    with open('tools/progresses_dump.json', 'w', encoding='utf-8') as f:
        json.dump(progresses_data, f, indent=2, ensure_ascii=False)

    # Stats
    print(f"Total Projects: {len(projects_data)}")
    print(f"Total Progresses: {len(progresses_data)}")
    
    # Analyze distinct Activities
    activities = set(p.get('Activity') for p in progresses_data)
    print(f"Distinct Activities: {activities}")

    # Analyze Progress Close values
    closes = set(p.get('Progress Close') for p in progresses_data)
    print(f"Distinct Progress Close values: {closes}")

    # Check JO No matching between projects and progresses
    proj_jos = set(p.get('JO No') for p in projects_data if p.get('JO No'))
    prog_jos = set(p.get('JO No') for p in progresses_data if p.get('JO No'))
    matched = prog_jos.intersection(proj_jos)
    unmatched = prog_jos - proj_jos
    print(f"Unique JOs in Projects: {len(proj_jos)}")
    print(f"Unique JOs in Progresses: {len(prog_jos)}")
    print(f"Matched JOs: {len(matched)}")
    print(f"Unmatched JOs in Progresses: {unmatched}")

analyze()
