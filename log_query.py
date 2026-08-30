import re

path = 'app/Http/Controllers/ConvenioController.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

content = content.replace(
    "$total = (clone $base)->count();",
    "\\Log::info('tussProcedimentos Query:', ['sql' => $base->toSql(), 'bindings' => $base->getBindings(), 'account_id' => auth()->user()->account_id ?? null, 'results' => $base->get()]);\n        $total = (clone $base)->count();"
)

with open(path, 'w', encoding='utf-8') as f:
    f.write(content)
