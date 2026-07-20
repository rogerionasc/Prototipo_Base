# Plano: Aba de Medicos Conveniados em Convenios

## Conclusao Da Pesquisa
- O modal `Adicionar Convenio` e a edicao usam [Index.vue](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/resources/js/Pages/Convenios/Index.vue) em conjunto com [Create.vue](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/resources/js/Pages/Convenios/Create.vue).
- A persistencia atual de convenios fica em [ConvenioController.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/app/Http/Controllers/ConvenioController.php) e hoje ja grava relacoes em `convenio_tuss`.
- O cadastro de medicos existe via [ProfissionalSaude.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/app/Models/ProfissionalSaude.php), que e a entidade correta para representar medicos conveniados.
- O model [Convenio.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/app/Models/Convenio.php) ainda nao expone relacionamento com medicos.

## Arquivos E Modulos A Editar
- [2026_07_18_000003_create_convenio_medicos_table.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/database/migrations/2026_07_18_000003_create_convenio_medicos_table.php)
- [Convenio.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/app/Models/Convenio.php)
- [ProfissionalSaude.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/app/Models/ProfissionalSaude.php)
- [ConvenioController.php](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/app/Http/Controllers/ConvenioController.php)
- [Index.vue](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/resources/js/Pages/Convenios/Index.vue)
- [Create.vue](file:///C:/laragon/www/Prototipo_Base/Prototipo_Base/resources/js/Pages/Convenios/Create.vue)

## Passos De Implementacao
1. Criar a tabela `convenio_medicos` com chaves para `convenios` e `profissionais_saude`, timestamps, soft delete e unicidade por par convenio/medico.
2. Adicionar relacionamento `belongsToMany` entre convenio e profissionais de saude nos models.
3. Expandir o `index()` de convenios para carregar a lista de profissionais disponiveis e os medicos vinculados em cada convenio.
4. Ajustar validacao e persistencia em `store()` e `update()` para aceitar `medico_ids` e sincronizar a tabela pivô.
5. Adicionar uma nova aba no modal `Adicionar Convenio` para selecionar medicos conveniados.
6. Garantir que o fluxo de edicao carregue os medicos ja vinculados e que o fluxo de criacao limpe a selecao ao reabrir o modal.
7. Validar no frontend que os ids selecionados sejam enviados junto com `tuss_ids` no mesmo submit.

## Dependencias E Consideracoes
- A fonte de medicos sera `profissionais_saude`; nao ha necessidade de criar novo cadastro base.
- O componente usa `Choices.js`, entao a aba nova deve seguir o mesmo padrao de sincronizacao Vue <-> Choices ja existente no formulario.
- A lista de medicos deve funcionar tanto no cadastro quanto na edicao sem quebrar a logica atual de procedimentos TUSS.
- Depois da implementacao sera necessario executar `php artisan migrate`.

## Riscos E Mitigacao
- Risco de duplicidade na pivô: mitigado com indice unico e normalizacao dos ids antes do insert.
- Risco de perder selecoes na edicao: mitigado carregando os medicos vinculados ao abrir o modal e sincronizando com o componente visual.
- Risco de regressao no formulario de convenio: mitigado preservando a estrutura atual das abas e limitando a mudanca ao novo campo `medico_ids`.
- Risco de inconsistencias frontend/backend: mitigado com validacao de `medico_ids.*` no controller e checagem final de diagnosticos apos editar os arquivos Vue/PHP.
