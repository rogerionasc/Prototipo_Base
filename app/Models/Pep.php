<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pep extends Model
{
    use HasFactory;

    protected $table = 'peps';

    protected $fillable = [
        'atendimento_id',
        'paciente_id',
        'profissional_id',
        'aberto_em',
        'encerrado_em',
        'status',
        'observacao',
        'created_by',
        'updated_by'
    ];

    public function atendimento()
    {
        return $this->belongsTo(Atendimento::class, 'atendimento_id');
    }

    public function anamnese()
    {
        return $this->hasOne(PepAnamnese::class, 'pep_id');
    }

    public function sinaisVitais()
    {
        return $this->hasMany(PepSinaisVitais::class, 'pep_id');
    }

    public function examesFisicos()
    {
        return $this->hasMany(PepExameFisico::class, 'pep_id');
    }

    public function diagnosticos()
    {
        return $this->hasMany(PepDiagnostico::class, 'pep_id');
    }

    public function evolucoes()
    {
        return $this->hasMany(PepEvolucao::class, 'pep_id');
    }

    public function procedimentos()
    {
        return $this->hasMany(PepProcedimento::class, 'pep_id');
    }

    public function prescricoes()
    {
        return $this->hasMany(PepPrescricao::class, 'pep_id');
    }

    public function solicitacoesExames()
    {
        return $this->hasMany(PepSolicitacaoExame::class, 'pep_id');
    }

    public function receitas()
    {
        return $this->hasMany(PepReceita::class, 'pep_id');
    }

    public function atestados()
    {
        return $this->hasMany(PepAtestado::class, 'pep_id');
    }

    public function encaminhamentos()
    {
        return $this->hasMany(PepEncaminhamento::class, 'pep_id');
    }

    public function documentos()
    {
        return $this->hasMany(PepDocumento::class, 'pep_id');
    }

    public function arquivos()
    {
        return $this->hasMany(PepArquivo::class, 'pep_id');
    }

    public function assinaturas()
    {
        return $this->hasMany(PepAssinatura::class, 'pep_id');
    }
}
