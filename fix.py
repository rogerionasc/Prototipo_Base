import re

path = 'app/Http/Controllers/ConvenioController.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace("\\'account_id\\' => ,", "'account_id' => $accountId,")

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
