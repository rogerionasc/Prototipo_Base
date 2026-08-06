import re

with open('resources/js/Pages/Consultorio/Pep/Show.vue', 'r', encoding='utf-8') as f:
    lines = f.readlines()

template_start = None
template_end = None
for i, line in enumerate(lines):
    if '<template>' in line and template_start is None:
        template_start = i
    if '</template>' in line:
        template_end = i

# Count div opens and closes per line
div_depth = 0
for i in range(template_start+1, template_end):
    line = lines[i]
    # Remove comments
    line_clean = re.sub(r'<!--.*?-->', '', line)
    
    opens = len(re.findall(r'<div\b', line_clean))
    closes = len(re.findall(r'</div>', line_clean))
    
    old_depth = div_depth
    div_depth += opens - closes
    
    if opens or closes:
        line_num = i + 1
        print(f"L{line_num:4d}: depth {old_depth:2d} -> {div_depth:2d} (opens={opens}, closes={closes}) | {line.rstrip()[:100]}")

print(f"\nFinal div depth: {div_depth}")
if div_depth != 0:
    print(f"ERROR: {abs(div_depth)} {'unclosed' if div_depth > 0 else 'extra closing'} div(s)")
else:
    print("OK: All divs balanced!")
