import zipfile
import xml.etree.ElementTree as ET
import json

def parse_xlsx(file_path):
    with zipfile.ZipFile(file_path) as z:
        # 1. Parse shared strings if present
        shared_strings = []
        if 'xl/sharedStrings.xml' in z.namelist():
            tree = ET.fromstring(z.read('xl/sharedStrings.xml'))
            ns = {'ns': 'http://schemas.openxmlformats.org/spreadsheetml/2006/main'}
            for si in tree.findall('ns:si', ns):
                t = si.find('ns:t', ns)
                if t is not None and t.text:
                    shared_strings.append(t.text)
                else:
                    # check rich text <r><t>
                    parts = [r.find('ns:t', ns).text for r in si.findall('ns:r', ns) if r.find('ns:t', ns) is not None and r.find('ns:t', ns).text]
                    shared_strings.append(''.join(parts))

        # 2. Parse sheet1.xml
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

for name in ['Table - Project List Master.xlsx', 'Table - Progress New model Master.xlsx']:
    path = f'import/{name}'
    rows = parse_xlsx(path)
    print(f"\n=================== {name} ===================")
    print(f"Total rows found: {len(rows)}")
    if rows:
        header_row = rows[0]
        print(f"Headers: {list(header_row.values())}")
        print("\nSample row 1:")
        print(json.dumps(rows[1] if len(rows) > 1 else {}, indent=2, ensure_ascii=False))
        if len(rows) > 2:
            print("\nSample row 2:")
            print(json.dumps(rows[2], indent=2, ensure_ascii=False))
