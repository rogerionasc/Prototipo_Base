<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const isScrolled = ref(false);
const visibleSections = ref(new Set());
const mobileMenuOpen = ref(false);

onMounted(() => {
  window.addEventListener('scroll', () => {
    isScrolled.value = window.scrollY > 50;
  });

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        visibleSections.value.add(entry.target.id);
      }
    });
  }, { threshold: 0.12 });

  document.querySelectorAll('.reveal-section').forEach(el => observer.observe(el));

  // Animate counters
  const counters = document.querySelectorAll('.counter-value');
  const animateCounter = (el) => {
    const target = parseInt(el.getAttribute('data-target'));
    const duration = 2000;
    const startTime = performance.now();
    const update = (currentTime) => {
      const progress = Math.min((currentTime - startTime) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(target * eased).toLocaleString('pt-BR');
      if (progress < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  };

  const counterObs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        counterObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  counters.forEach(el => counterObs.observe(el));

  // Smooth scroll for anchors
  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const id = a.getAttribute('href').slice(1);
      const target = document.getElementById(id);
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      mobileMenuOpen.value = false;
    });
  });
});

const modules = [
  { icon: 'ri-calendar-check-line', title: 'Agendamento Online', desc: 'Agenda inteligente com envio automático de lembretes via WhatsApp. Reduza faltas e organize os horários dos seus profissionais de saúde.', color: '#0ea5e9' },
  { icon: 'ri-heart-pulse-line', title: 'Prontuário Eletrônico (PEP)', desc: 'Anamnese, evolução clínica, prescrições e diagnósticos CID-10 integrados. Histórico completo do paciente em um só lugar.', color: '#ef4444' },
  { icon: 'ri-file-paper-2-line', title: 'Faturamento TISS/ANS', desc: 'Gere lotes XML no padrão TISS da ANS. Acompanhe glosas, reenvie lotes e controle o repasse de convênios em tempo real.', color: '#8b5cf6' },
  { icon: 'ri-money-dollar-box-line', title: 'Financeiro & Cobrança', desc: 'Contas a receber, fluxo de caixa e cobranças automáticas via Pix, Boleto e Cartão. Links de pagamento para pacientes particulares.', color: '#f59e0b' },
  { icon: 'ri-shield-cross-line', title: 'Gestão de Convênios', desc: 'Cadastre operadoras, tabelas de procedimentos TUSS e gerencie autorizações. Controle prazos de retorno e dias para faturamento.', color: '#10b981' },
  { icon: 'ri-user-heart-fill', title: 'Cadastro de Pacientes', desc: 'Ficha completa com dados pessoais, plano de saúde, histórico de consultas e comunicação integrada via SMS e e-mail.', color: '#06b6d4' },
  { icon: 'ri-stethoscope-line', title: 'Consultório Inteligente', desc: 'Fila de atendimento, chamada de pacientes por painel, controle de salas e integração com totem de autoatendimento.', color: '#e11d48' },
  { icon: 'ri-bar-chart-box-line', title: 'Relatórios & Dashboards', desc: 'Indicadores em tempo real: faturamento, ocupação de agenda, produtividade médica e análise financeira por período.', color: '#7c3aed' },
];

const workflows = [
  { icon: 'ri-door-open-line', title: 'Recepção', desc: 'Paciente chega, faz check-in pelo totem ou recepcionista e entra na fila de espera digital.', step: '01' },
  { icon: 'ri-stethoscope-line', title: 'Consulta', desc: 'Médico chama o paciente, abre o PEP, registra anamnese, evolução e prescrições.', step: '02' },
  { icon: 'ri-file-list-3-line', title: 'Faturamento', desc: 'Guias TISS são geradas automaticamente. Lotes XML são enviados às operadoras de saúde.', step: '03' },
  { icon: 'ri-money-dollar-circle-line', title: 'Recebimento', desc: 'Acompanhe repasses, identifique glosas e receba via Pix, boleto ou cartão de crédito.', step: '04' },
];

const testimonials = [
  { name: 'Dra. Mariana Costa', role: 'Cardiologista — Clínica CardioVida', text: 'O prontuário eletrônico é extremamente completo. Consigo acessar todo o histórico do paciente de forma rápida e segura, melhorando muito a qualidade do atendimento.', avatar: 'M', color: '#0ea5e9' },
  { name: 'Dr. Felipe Andrade', role: 'Ortopedista — Centro Ortopédico SP', text: 'O faturamento TISS nos economiza horas por semana. O que antes era feito manualmente em planilhas, agora é automático e sem erros.', avatar: 'F', color: '#10b981' },
  { name: 'Patrícia Mendes', role: 'Gestora Administrativa — Clínica Saúde Total', text: 'A gestão financeira mudou completamente. Agora tenho visibilidade real do fluxo de caixa, receitas e inadimplência. É essencial.', avatar: 'P', color: '#8b5cf6' },
];
</script>

<template>
  <Head title="WebClinic — Gestão Médica Inteligente" />

  <div class="lp">
    <!-- ===== NAVBAR ===== -->
    <nav class="lp-nav" :class="{ scrolled: isScrolled }">
      <div class="lp-w">
        <div class="lp-nav-inner">
          <div class="lp-nav-brand">
            <img src="/storage/logo-sistema/Logo-Top.svg" alt="Logo" height="42" />
            <img src="/storage/logo-sistema/Logo-Bottom.svg" alt="WebClinic" height="48" style="margin-top: 2px;" />
          </div>
          <div class="lp-nav-links d-none d-md-flex">
            <a href="#modulos">Módulos</a>
            <a href="#fluxo">Fluxo Clínico</a>
            <a href="#precos">Preços</a>
          </div>
          <div class="lp-nav-actions">
            <Link href="/login" class="lp-btn btn-outline-teal">Acessar Sistema</Link>
            <button class="lp-hamburger d-md-none" @click="mobileMenuOpen = !mobileMenuOpen" :class="{ scrolled: isScrolled }">
              <i :class="mobileMenuOpen ? 'ri-close-line' : 'ri-menu-line'"></i>
            </button>
          </div>
        </div>
        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="lp-mobile-menu">
          <a href="#modulos">Módulos</a>
          <a href="#fluxo">Fluxo Clínico</a>
          <a href="#precos">Preços</a>
          <Link href="/login" class="lp-btn btn-teal w-100 mt-2">Acessar Sistema</Link>
        </div>
      </div>
    </nav>

    <!-- ===== HERO PROSURANCE STYLE ===== -->
    <section class="lp-hero-pro">
      <div class="lp-w lp-hero-pro-inner">
        <div class="lp-hero-pro-card">
          <div class="lp-hero-pro-grid">
            
            <!-- Left Column: Content -->
            <div class="lp-hero-pro-content">
              <h1>A plataforma completa para gestão da <span class="lp-hl-dark">sua clínica</span></h1>
              <p class="lp-hero-pro-sub">Prontuário eletrônico, faturamento TISS, gestão financeira e controle de convênios integrados em uma única solução rápida e fácil de usar.</p>
              
              <div class="lp-hero-pro-action-row">
                <Link href="/register" class="lp-btn lp-btn-pill btn-dark-teal">Começar Agora</Link>
                
                <div class="lp-hero-pro-social">
                  <div class="lp-avatars">
                    <img src="https://i.pravatar.cc/100?img=32" alt="Avatar 1">
                    <img src="https://i.pravatar.cc/100?img=68" alt="Avatar 2">
                    <img src="https://i.pravatar.cc/100?img=47" alt="Avatar 3">
                  </div>
                  <span>Mais de <strong>500 clínicas</strong><br>cadastradas</span>
                </div>
              </div>
            </div>

            <!-- Right Column: Image with Arch -->
            <div class="lp-hero-pro-visual">
              <div class="lp-arch-bg">
                <img src="/images/hero_doctor_nobg.png" alt="Médica" class="lp-doctor-img" />
              </div>
            </div>

          </div>
        </div>

        <!-- Floating Bottom Dock -->
        <div class="lp-hero-pro-dock">
          <div class="lp-dock-item">
            <div class="lp-dock-icon"><i class="ri-check-line"></i></div>
            <div class="lp-dock-text">
              <strong>Padrão TISS/ANS</strong>
              <span>Integração nativa</span>
            </div>
          </div>
          <div class="lp-dock-divider"></div>
          <div class="lp-dock-item">
            <div class="lp-dock-icon"><i class="ri-shield-check-line"></i></div>
            <div class="lp-dock-text">
              <strong>LGPD Compliance</strong>
              <span>Dados protegidos</span>
            </div>
          </div>
          <div class="lp-dock-divider"></div>
          <div class="lp-dock-item">
            <div class="lp-dock-icon"><i class="ri-bar-chart-box-line"></i></div>
            <div class="lp-dock-text">
              <strong>Gestão Completa</strong>
              <span>Financeiro e PEP</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== ESPECIALIDADES / PARA QUEM ===== -->
    <!-- 
    <section class="lp-section lp-for-who reveal-section" id="para-quem" :class="{ visible: visibleSections.has('para-quem') }">
      <div class="lp-w">
        <div class="lp-for-who-grid">
          <div class="lp-for-who-item" v-for="(item, i) in [
            { icon: 'ri-hospital-line', label: 'Clínicas Médicas' },
            { icon: 'ri-mental-health-line', label: 'Consultórios' },
            { icon: 'ri-building-2-line', label: 'Centros de Saúde' },
            { icon: 'ri-tooth-line', label: 'Odontologia' },
            { icon: 'ri-eye-line', label: 'Oftalmologia' },
            { icon: 'ri-psychotherapy-line', label: 'Psicologia' },
          ]" :key="i" :style="`--d:${i * 0.08}s`">
            <i :class="item.icon"></i>
            <span>{{ item.label }}</span>
          </div>
        </div>
      </div>
    </section>
    -->

    <!-- ===== MÓDULOS ===== -->
    <section id="modulos" class="lp-section reveal-section" :class="{ visible: visibleSections.has('modulos') }">
      <div class="lp-w">
        <div class="lp-sh">
          <h2>Tudo que sua clínica precisa para <span class="lp-hl-dark">funcionar com excelência</span></h2>
          <p>Módulos integrados pensados para cada etapa do fluxo de atendimento clínico — do cadastro do paciente ao recebimento financeiro.</p>
        </div>
        <div class="lp-modules-grid">
          <div v-for="(mod, i) in modules" :key="i" class="lp-mod" :style="`--d:${i * 0.08}s`">
            <div class="lp-mod-icon" :style="`color: ${mod.color};`">
              <i :class="mod.icon"></i>
            </div>
            <h3>{{ mod.title }}</h3>
            <p>{{ mod.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FLUXO CLÍNICO ===== -->
    <section id="fluxo" class="lp-section lp-section-alt reveal-section" :class="{ visible: visibleSections.has('fluxo') }">
      <div class="lp-w">
        <div class="lp-sh">
          <h2>Do check-in ao <span class="lp-hl-dark">recebimento</span></h2>
          <p>Acompanhe como o WebClinic integra todo o ciclo clínico de ponta a ponta.</p>
        </div>
        <div class="lp-flow">
          <div v-for="(wf, i) in workflows" :key="i" class="lp-flow-step" :style="`--d:${i * 0.12}s`">
            <div class="lp-flow-num">{{ wf.step }}</div>
            <div class="lp-flow-line" v-if="i < workflows.length - 1"></div>
            <div class="lp-flow-icon"><i :class="wf.icon"></i></div>
            <h3>{{ wf.title }}</h3>
            <p>{{ wf.desc }}</p>
          </div>
        </div>
      </div>
    </section>


    <!-- ===== PREÇOS ===== -->
    <section class="lp-section reveal-section" id="precos" :class="{ visible: visibleSections.has('precos') }">
      <div class="lp-w">
        <div class="lp-sh text-center" style="text-align: center; margin-bottom: 50px;">
          <h2 style="text-align: center;">Planos transparentes para sua <span class="lp-hl-dark">clínica</span></h2>
          <p style="text-align: center; margin: 0 auto;">Escolha o plano ideal para o tamanho e a necessidade do seu negócio, sem taxas ocultas.</p>
        </div>
        <div class="lp-pricing-grid">
          <!-- Básico -->
          <div class="lp-pricing-card">
            <div class="lp-pricing-header">
              <h3>Básico</h3>
              <p>Para profissionais independentes</p>
              <div class="lp-price">
                <span class="currency">R$</span>
                <span class="value">97</span>
                <span class="period">/mês</span>
              </div>
            </div>
            <ul class="lp-pricing-features">
              <li><i class="ri-check-line"></i> 1 Profissional de Saúde</li>
              <li><i class="ri-check-line"></i> Agenda Online</li>
              <li><i class="ri-check-line"></i> Prontuário Eletrônico (PEP)</li>
              <li><i class="ri-close-line text-muted"></i> Faturamento TISS</li>
              <li><i class="ri-close-line text-muted"></i> Relatórios Financeiros</li>
            </ul>
            <a href="#" class="lp-btn btn-outline-teal w-100 justify-content-center">Começar Grátis</a>
          </div>

          <!-- Profissional (Highlight) -->
          <div class="lp-pricing-card popular">
            <div class="lp-pricing-badge">Mais Escolhido</div>
            <div class="lp-pricing-header">
              <h3>Profissional</h3>
              <p>Para clínicas em crescimento</p>
              <div class="lp-price">
                <span class="currency">R$</span>
                <span class="value">197</span>
                <span class="period">/mês</span>
              </div>
            </div>
            <ul class="lp-pricing-features">
              <li><i class="ri-check-line"></i> Até 5 Profissionais</li>
              <li><i class="ri-check-line"></i> Agenda Online Avançada</li>
              <li><i class="ri-check-line"></i> Prontuário Eletrônico (PEP)</li>
              <li><i class="ri-check-line"></i> Faturamento TISS/SADT</li>
              <li><i class="ri-check-line"></i> Relatórios Financeiros</li>
            </ul>
            <a href="#" class="lp-btn btn-teal w-100 justify-content-center">Assinar Agora</a>
          </div>

          <!-- Premium -->
          <div class="lp-pricing-card">
            <div class="lp-pricing-header">
              <h3>Premium</h3>
              <p>Para redes e grandes clínicas</p>
              <div class="lp-price">
                <span class="currency">R$</span>
                <span class="value">397</span>
                <span class="period">/mês</span>
              </div>
            </div>
            <ul class="lp-pricing-features">
              <li><i class="ri-check-line"></i> Profissionais Ilimitados</li>
              <li><i class="ri-check-line"></i> Todas as ferramentas</li>
              <li><i class="ri-check-line"></i> Suporte Prioritário 24/7</li>
              <li><i class="ri-check-line"></i> API de Integração</li>
              <li><i class="ri-check-line"></i> Gestão Multi-unidades</li>
            </ul>
            <a href="#" class="lp-btn btn-outline-teal w-100 justify-content-center">Falar com Consultor</a>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="lp-cta">
      <div class="lp-w lp-cta-inner">
        <div class="lp-cta-icon"><i class="ri-heart-pulse-fill"></i></div>
        <h2>Pronto para transformar a gestão da sua clínica?</h2>
        <p>Comece agora mesmo — configuração rápida, sem necessidade de cartão de crédito. Teste todos os módulos gratuitamente.</p>
        <div class="lp-cta-btns">
          <Link href="/login" class="lp-btn btn-dark-teal btn-lg" style="border-radius: 100px; padding: 16px 36px; font-size: 16px;"><i class="ri-computer-line"></i> Testar o Sistema</Link>
        </div>
      </div>
    </section>

    <!-- ===== SEGURANÇA ===== -->
    <section class="lp-section lp-section-alt reveal-section" id="seguranca" :class="{ visible: visibleSections.has('seguranca') }" style="position: relative; overflow: hidden;">
      <i class="ri-shield-check-fill lp-sec-bg-icon"></i>
      <div class="lp-w" style="position: relative; z-index: 2;">
        <div class="lp-security-grid">
          <div class="lp-security-text">
            <h2>Seus dados protegidos com os <span class="lp-hl-dark">mais altos padrões</span></h2>
            <p>Sabemos que dados de saúde exigem proteção máxima. O WebClinic foi construído desde o início com foco em segurança e conformidade regulatória.</p>
            <div class="lp-security-items">
              <div class="lp-sec-item" v-for="(item, i) in [
                { icon: 'ri-shield-check-line', text: 'Conformidade total com a LGPD (Lei Geral de Proteção de Dados)' },
                { icon: 'ri-lock-password-line', text: 'Criptografia de ponta a ponta em todos os dados sensíveis' },
                { icon: 'ri-cloud-line', text: 'Backup automático diário com retenção de 90 dias' },
                { icon: 'ri-user-settings-line', text: 'Controle de acesso por perfis e permissões granulares' },
              ]" :key="i">
                <i :class="item.icon"></i>
                <span>{{ item.text }}</span>
              </div>
            </div>
          </div>
          <div class="lp-security-visual d-none d-lg-flex">
            <div class="lp-sec-shield">
              <i class="ri-shield-check-fill"></i>
              <span>Dados<br>Protegidos</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="lp-footer">
      <div class="lp-w">
        <div class="lp-footer-grid">
          <div class="lp-footer-brand">
            <div class="lp-footer-logo">
              <img src="/storage/logo-sistema/Logo-Top.svg" alt="" height="28" style="filter: brightness(0) invert(1);" />
              <img src="/storage/logo-sistema/Logo-Bottom.svg" alt="" height="32" style="filter: brightness(0) invert(1);" />
            </div>
            <p>Gestão Médica Inteligente.<br>Tecnologia que cuida de quem cuida.</p>
          </div>
          <div class="lp-footer-cols">
            <div>
              <h4>Produto</h4>
              <a href="#modulos">Módulos</a>
              <a href="#fluxo">Fluxo Clínico</a>
            </div>
            <div>
              <h4>Suporte</h4>
              <a href="#">Central de Ajuda</a>
              <a href="#">Contato</a>
              <a href="#">Status do Sistema</a>
            </div>
            <div>
              <h4>Legal</h4>
              <a href="/terms-of-service">Termos de Uso</a>
              <a href="/privacy-policy">Política de Privacidade</a>
              <a href="#">LGPD</a>
            </div>
          </div>
        </div>
        <div class="lp-footer-bottom">
          <p>&copy; {{ new Date().getFullYear() }} WebClinic. Todos os direitos reservados. Desenvolvido por <strong>WCode</strong>.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* ===== BASE ===== */
.lp {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  color: #1e293b;
  overflow-x: hidden;
  background: #fff;
  -webkit-font-smoothing: antialiased;
}
.lp-w { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

/* ===== NAVBAR ===== */
.lp-nav { position: absolute; top: 0; left: 0; width: 100%; z-index: 999; padding: 24px 0; transition: all .3s ease; }
.lp-nav.scrolled { position: fixed; padding: 16px 0; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0,0,0,.05); }
.lp-nav-inner { display: flex; align-items: center; justify-content: space-between; }
.lp-nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
.lp-nav-links { display: flex; gap: 28px; }
.lp-nav-links a { color: #475569; text-decoration: none; font-size: 14px; font-weight: 500; transition: color .2s; }
.lp-nav-links a:hover { color: #0ab39c; }
.lp-nav-actions { display: flex; align-items: center; gap: 12px; }
.lp-hamburger { background: none; border: none; color: #1e293b; font-size: 24px; cursor: pointer; }
.lp-mobile-menu { background: rgba(255,255,255,.98); backdrop-filter: blur(16px); border-radius: 12px; padding: 16px; margin-top: 12px; display: flex; flex-direction: column; gap: 8px; }
.lp-mobile-menu a { color: #334155; text-decoration: none; padding: 10px 16px; border-radius: 8px; font-weight: 500; font-size: 15px; }
.lp-mobile-menu a:hover { background: #f1f5f9; }

/* ===== BUTTONS ===== */
.lp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px; border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none; transition: all .25s; border: none; cursor: pointer; white-space: nowrap; }
.btn-lg { padding: 14px 30px; font-size: 16px; }
.btn-teal { background: linear-gradient(135deg, #0ab39c, #099885); color: #fff; box-shadow: 0 4px 14px rgba(10,179,156,.35); }
.btn-teal:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(10,179,156,.45); color: #fff; }
.btn-outline-white { background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.3); }
.btn-outline-white:hover { background: rgba(255,255,255,.22); color: #fff; }
.btn-outline-teal { background: transparent; color: #0ab39c; border: 1px solid #0ab39c; }
.btn-outline-teal:hover { background: #0ab39c; color: #fff; }
.btn-ghost-white { background: transparent; color: rgba(255,255,255,.9); }
.btn-ghost-white:hover { color: #fff; background: rgba(255,255,255,.1); }
.btn-white { background: #fff; color: #0ab39c; box-shadow: 0 4px 14px rgba(0,0,0,.15); }
.btn-white:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.2); color: #0ab39c; }

/* ===== HERO PROSURANCE STYLE ===== */
.lp-hero-pro { 
  background-color: #F4F6F9;
  background-image: radial-gradient(rgba(15, 23, 42, 0.12) 1.5px, transparent 1.5px);
  background-size: 32px 32px;
  background-position: -16px -16px;
  padding: 140px 0 80px; min-height: auto; position: relative; overflow: hidden;
}
.lp-hero-pro::before {
  content: ''; position: absolute; top: 0; left: 0; width: 60%; height: 100%;
  background: linear-gradient(135deg, rgba(10, 179, 156, 0.08) 0%, rgba(10, 179, 156, 0.01) 100%);
  clip-path: polygon(0 0, 100% 0, 75% 100%, 0% 100%);
  z-index: 0;
}
.lp-hero-pro::after {
  content: ''; position: absolute; top: -5%; right: -10%; width: 500px; height: 500px;
  border-radius: 50%; border: 60px solid rgba(10, 179, 156, 0.08);
  z-index: 0;
}
.lp-hero-pro-inner { position: relative; z-index: 1; }
.lp-hero-pro-card { background: transparent; position: relative; padding: 20px 0 60px; }

.lp-hero-pro-grid { display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 40px; align-items: center; }

/* Left Content */
.lp-hero-pro-content h1 { font-size: 52px; font-weight: 900; color: #0F172A; line-height: 1.1; margin-bottom: 24px; letter-spacing: -1.5px; }
.lp-hero-pro-sub { font-size: 18px; color: #64748B; line-height: 1.6; margin-bottom: 40px; max-width: 520px; }
.lp-hl-dark { color: #0ab39c; }

.lp-hero-pro-action-row { display: flex; align-items: center; gap: 32px; flex-wrap: wrap; }
.btn-dark-teal { background: #042f2e; color: #fff; padding: 16px 36px; border-radius: 100px; font-size: 16px; font-weight: 600; box-shadow: 0 10px 25px rgba(4, 47, 46, 0.2); transition: all 0.3s; }
.btn-dark-teal:hover { background: #0ab39c; color: #fff; transform: translateY(-3px); box-shadow: 0 12px 30px rgba(10, 179, 156, 0.3); }

.lp-hero-pro-social { display: flex; align-items: center; gap: 16px; }
.lp-avatars { display: flex; }
.lp-avatars img { width: 44px; height: 44px; border-radius: 50%; border: 3px solid #fff; margin-left: -12px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
.lp-avatars img:first-child { margin-left: 0; }
.lp-hero-pro-social span { font-size: 13px; color: #64748B; line-height: 1.4; }
.lp-hero-pro-social strong { color: #0F172A; font-weight: 700; }

/* Right Visual */
.lp-hero-pro-visual { display: flex; justify-content: center; position: relative; }
.lp-arch-bg { width: 100%; max-width: 380px; height: 480px; background: transparent; position: relative; display: flex; justify-content: center; overflow: visible; margin-top: 40px; }
.lp-doctor-img { position: absolute; bottom: 0; max-height: 115%; width: auto; object-fit: contain; z-index: 2; }

/* Floating Dock */
.lp-hero-pro-dock { background: #fff; border-radius: 24px; padding: 24px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 15px 40px rgba(15, 23, 42, 0.08); width: calc(100% - 40px); max-width: 1080px; margin: -90px auto 0; position: relative; z-index: 10; }
.lp-dock-item { display: flex; align-items: center; gap: 16px; flex: 1; justify-content: center; }
.lp-dock-icon { width: 48px; height: 48px; background: #f1f5f9; color: #0ab39c; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
.lp-dock-text { display: flex; flex-direction: column; }
.lp-dock-text strong { font-size: 15px; color: #0F172A; font-weight: 700; }
.lp-dock-text span { font-size: 13px; color: #64748B; }
.lp-dock-divider { width: 1px; height: 40px; background: #E2E8F0; }

/* ===== SECTIONS ===== */
.lp-section { padding: 100px 0; }
.lp-section-alt { background: #f8fafc; }
.lp-sh { text-align: center; max-width: 640px; margin: 0 auto 56px; }
.lp-sh h2 { font-size: 36px; font-weight: 800; color: #0f172a; margin-bottom: 16px; line-height: 1.2; }
.lp-sh p { font-size: 17px; color: #64748b; line-height: 1.7; }
.lp-sh-white h2 { color: #fff; }
.lp-sh-white p { color: rgba(255,255,255,.75); }
.lp-tag { display: inline-flex; align-items: center; gap: 6px; background: #0ab39c12; color: #0ab39c; font-size: 13px; font-weight: 600; padding: 6px 16px; border-radius: 100px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: .5px; }
.lp-tag-white { background: rgba(255,255,255,.15); color: #fff; }

/* ===== FOR WHO ===== */
.lp-for-who { padding: 50px 0 30px; }
.lp-for-who-grid { display: flex; justify-content: center; gap: 40px; flex-wrap: wrap; }
.lp-for-who-item { display: flex; align-items: center; gap: 10px; color: #64748b; font-size: 15px; font-weight: 500; opacity: 0; transform: translateY(15px); }
.visible .lp-for-who-item { opacity: 1; transform: translateY(0); transition: all .4s ease; transition-delay: var(--d); }
.lp-for-who-item i { font-size: 24px; color: #0ab39c; }

/* ===== MODULES ===== */
.lp-modules-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
.lp-mod { background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; padding: 32px 28px; transition: all .3s ease; opacity: 0; transform: translateY(30px); }
.visible .lp-mod { opacity: 1; transform: translateY(0); transition-delay: var(--d); }
.lp-mod:hover { border-color: #e2e8f0; box-shadow: 0 12px 32px rgba(15,23,42,.04); }
.lp-mod-icon { font-size: 28px; margin-bottom: 24px; line-height: 1; display: inline-block; }
.lp-mod h3 { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.2px; }
.lp-mod p { font-size: 14px; color: #64748b; line-height: 1.6; margin: 0; }

/* ===== FLOW ===== */
.lp-flow { display: flex; justify-content: center; gap: 40px; }
.lp-flow-step { text-align: center; flex: 1; max-width: 240px; position: relative; opacity: 0; transform: translateY(30px); }
.visible .lp-flow-step { opacity: 1; transform: translateY(0); transition: all .5s ease; transition-delay: var(--d); }
.lp-flow-num { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #0ab39c, #099885); color: #fff; font-size: 18px; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 14px rgba(10,179,156,.25); }
.lp-flow-line { position: absolute; top: 24px; left: calc(50% + 30px); width: calc(100% - 10px); height: 2px; background: repeating-linear-gradient(90deg, #0ab39c 0, #0ab39c 5px, transparent 5px, transparent 10px); opacity: .3; }
.lp-flow-icon { font-size: 28px; color: #0ab39c; margin-bottom: 12px; }
.lp-flow-step h3 { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
.lp-flow-step p { font-size: 13px; color: #64748b; line-height: 1.7; }

/* ===== NUMBERS ===== */
.lp-numbers { background: linear-gradient(135deg, #0ab39c, #078a76, #066e5f); padding: 90px 0; }
.lp-num-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; text-align: center; }
.lp-num-card { opacity: 0; transform: translateY(20px); }
.visible .lp-num-card { opacity: 1; transform: translateY(0); transition: all .5s ease .2s; }
.lp-num-icon { font-size: 32px; color: rgba(255,255,255,.6); display: block; margin-bottom: 12px; }
.lp-num-value { display: flex; align-items: baseline; justify-content: center; }
.counter-value { font-size: 48px; font-weight: 800; color: #fff; line-height: 1; }
.lp-num-suffix { font-size: 24px; font-weight: 700; color: rgba(255,255,255,.8); margin-left: 2px; }
.lp-num-label { display: block; color: rgba(255,255,255,.7); font-size: 14px; font-weight: 500; margin-top: 8px; }

/* ===== TESTIMONIALS ===== */
.lp-test-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.lp-test { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; opacity: 0; transform: translateY(30px); transition: all .5s ease; }
.visible .lp-test { opacity: 1; transform: translateY(0); transition-delay: var(--d); }
.lp-test:hover { box-shadow: 0 8px 30px rgba(0,0,0,.06); }
.lp-test-stars { color: #f59e0b; font-size: 15px; margin-bottom: 14px; display: flex; gap: 2px; }
.lp-test-text { font-size: 14px; color: #475569; line-height: 1.75; margin-bottom: 20px; font-style: italic; }
.lp-test-author { display: flex; align-items: center; gap: 12px; }
.lp-test-avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; color: #fff; flex-shrink: 0; }
.lp-test-author div { display: flex; flex-direction: column; }
.lp-test-author strong { font-size: 14px; color: #0f172a; }
.lp-test-author span { font-size: 12px; color: #94a3b8; }

/* ===== SECURITY ===== */
.lp-sec-bg-icon { position: absolute; top: 50%; left: -50px; transform: translateY(-50%) rotate(-15deg); font-size: 450px; color: #0ab39c; opacity: 0.05; z-index: 0; pointer-events: none; }
.lp-security-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 60px; align-items: center; }
.lp-security-text h2 { text-align: left; font-size: 32px; }
.lp-security-text p { text-align: left; color: #64748b; font-size: 16px; line-height: 1.7; margin-bottom: 28px; }
.lp-security-items { display: flex; flex-direction: column; gap: 16px; }
.lp-sec-item { display: flex; align-items: flex-start; gap: 12px; font-size: 15px; color: #334155; }
.lp-sec-item i { color: #0ab39c; font-size: 20px; margin-top: 2px; flex-shrink: 0; }
.lp-security-visual { display: flex; align-items: center; justify-content: center; }
.lp-sec-shield { width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, rgba(10,179,156,0.1), rgba(10,179,156,0.15)); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; position: relative; z-index: 1; animation: floatShield 5s ease-in-out infinite; }
.lp-sec-shield::before, .lp-sec-shield::after { content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0; border-radius: 50%; border: 2px solid #0ab39c; opacity: 0; z-index: -1; animation: radarRipple 4s cubic-bezier(0.16, 1, 0.3, 1) infinite; }
.lp-sec-shield::after { animation-delay: 2s; }
.lp-sec-shield i { font-size: 56px; color: #0ab39c; }
.lp-sec-shield span { font-size: 14px; font-weight: 600; color: #0ab39c; text-align: center; line-height: 1.3; }
@keyframes floatShield { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
@keyframes radarRipple { 0% { transform: scale(0.95); opacity: 0.5; } 100% { transform: scale(1.6); opacity: 0; } }

/* ===== CTA ===== */
.lp-cta { 
  background-color: #F4F6F9;
  background-image: radial-gradient(rgba(15, 23, 42, 0.12) 1.5px, transparent 1.5px);
  background-size: 32px 32px;
  background-position: -16px -16px;
  padding: 100px 0; 
  text-align: center; 
  position: relative; 
  overflow: hidden; 
}
.lp-cta::before {
  content: ''; position: absolute; bottom: -10%; left: -5%; width: 400px; height: 400px;
  background: linear-gradient(135deg, rgba(10, 179, 156, 0.1) 0%, transparent 100%);
  clip-path: polygon(50% 0%, 0% 100%, 100% 100%); /* Triângulo */
  transform: rotate(-15deg);
  z-index: 0; pointer-events: none;
}
.lp-cta::after {
  content: ''; position: absolute; top: -10%; right: 5%; width: 250px; height: 250px;
  border: 4px solid rgba(10, 179, 156, 0.1);
  border-radius: 20px;
  transform: rotate(25deg); /* Quadrado inclinado */
  z-index: 0; pointer-events: none;
}
.lp-cta-inner { 
  position: relative; 
  z-index: 1;
}
.lp-cta-icon { font-size: 48px; color: #0ab39c; margin-bottom: 20px; position: relative; z-index: 1; }
.lp-cta h2 { font-size: 38px; font-weight: 800; color: #0F172A; margin-bottom: 16px; position: relative; z-index: 1; letter-spacing: -1px; }
.lp-cta p { font-size: 17px; color: #64748B; margin-bottom: 36px; max-width: 540px; margin-left: auto; margin-right: auto; position: relative; z-index: 1; }
.lp-cta-btns { display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; position: relative; z-index: 1; }

/* ===== FOOTER ===== */
.lp-footer { background: #0f172a; padding-top: 70px; }
.lp-footer-grid { display: grid; grid-template-columns: 1.2fr 2fr; gap: 50px; padding-bottom: 50px; border-bottom: 1px solid #1e293b; }
.lp-footer-brand p { color: #94a3b8; font-size: 14px; line-height: 1.7; margin-top: 16px; }
.lp-footer-logo { display: flex; align-items: center; gap: 6px; }
.lp-footer-cols { display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; }
.lp-footer-cols h4 { color: #fff; font-size: 13px; font-weight: 600; margin-bottom: 18px; text-transform: uppercase; letter-spacing: .5px; }
.lp-footer-cols a { display: block; color: #94a3b8; text-decoration: none; font-size: 14px; margin-bottom: 10px; transition: color .2s; }
.lp-footer-cols a:hover { color: #0ab39c; }
.lp-footer-bottom { padding: 22px 0; }
.lp-footer-bottom p { color: #64748b; font-size: 13px; text-align: center; margin: 0; }

/* ===== PRICING ===== */
.lp-pricing-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; align-items: center; margin: 50px auto 0; max-width: 1000px; }
.lp-pricing-card { background: #fff; border-radius: 20px; padding: 32px 24px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.04); border: 1px solid #E2E8F0; transition: transform 0.3s; position: relative; }
.lp-pricing-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
.lp-pricing-card.popular { border: 2px solid #0ab39c; box-shadow: 0 20px 50px rgba(10, 179, 156, 0.15); padding: 40px 24px; transform: scale(1.03); z-index: 2; }
.lp-pricing-card.popular:hover { transform: scale(1.03) translateY(-4px); }
.lp-pricing-badge { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: #0ab39c; color: #fff; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px; white-space: nowrap; }
.lp-pricing-header { text-align: center; margin-bottom: 24px; border-bottom: 1px solid #F1F5F9; padding-bottom: 24px; }
.lp-pricing-header h3 { font-size: 20px; color: #0F172A; font-weight: 700; margin-bottom: 6px; }
.lp-pricing-header p { font-size: 13px; color: #64748B; margin: 0 0 16px; }
.lp-price { display: flex; align-items: baseline; justify-content: center; color: #0F172A; }
.lp-price .currency { font-size: 16px; font-weight: 600; margin-right: 4px; }
.lp-price .value { font-size: 44px; font-weight: 800; line-height: 1; letter-spacing: -1.5px; }
.lp-price .period { font-size: 13px; color: #64748B; margin-left: 4px; }
.lp-pricing-features { list-style: none; padding: 0; margin: 0 0 30px; }
.lp-pricing-features li { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; font-size: 14px; color: #334155; }
.lp-pricing-features li i { font-size: 18px; color: #0ab39c; }
.lp-pricing-features li i.ri-close-line { color: #CBD5E1; }

/* ===== ANIMATIONS ===== */
@keyframes fadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeRight { from { opacity: 0; transform: translateX(40px); } to { opacity: 1; transform: translateX(0); } }

.reveal-section .lp-mod, .reveal-section .lp-flow-step, .reveal-section .lp-test, .reveal-section .lp-num-card, .reveal-section .lp-for-who-item { transition: all .6s cubic-bezier(.16,1,.3,1); }

/* ===== RESPONSIVE ===== */
@media (max-width: 991px) {
  .lp-hero-pro-grid { grid-template-columns: 1fr; text-align: center; gap: 40px; }
  .lp-hero-pro-card { padding: 0 0 40px; }
  .lp-hero-pro-content { padding-bottom: 0; }
  .lp-hero-pro-content h1 { font-size: 40px; }
  .lp-hero-pro-sub { margin: 0 auto 32px; }
  .lp-hero-pro-action-row { justify-content: center; flex-direction: column; gap: 24px; }
  .lp-hero-pro-dock { flex-direction: column; gap: 24px; padding-top: 40px; }
  .lp-dock-divider { display: none; }
  .lp-dock-item, .lp-dock-item:nth-child(3), .lp-dock-item:nth-child(5) { width: 100%; justify-content: center; }
  
  .lp-pricing-grid { grid-template-columns: 1fr; max-width: 450px; margin: 50px auto 0; gap: 40px; }
  .lp-pricing-card.popular { transform: scale(1); padding: 40px; }
  .lp-pricing-card.popular:hover { transform: translateY(-5px); }

  .lp-modules-grid { grid-template-columns: repeat(2, 1fr); }
  .lp-flow { flex-direction: column; align-items: center; gap: 32px; }
  .lp-test-grid { grid-template-columns: 1fr; max-width: 480px; margin: 0 auto; }
  .lp-flow { flex-direction: column; align-items: center; gap: 32px; }
  .lp-flow-line { display: none; }
  .lp-security-grid { grid-template-columns: 1fr; }
  .lp-footer-grid { grid-template-columns: 1fr; }
}

@media (max-width: 575px) {
  .lp-hero-pro-content h1 { font-size: 32px; letter-spacing: -0.5px; }
  .lp-sh h2 { font-size: 26px; }
  .lp-cta h2 { font-size: 26px; }
  .lp-modules-grid { grid-template-columns: 1fr; }
  .lp-num-grid { grid-template-columns: 1fr; }
  .counter-value { font-size: 36px; }
  .lp-footer-cols { grid-template-columns: 1fr; }
  .lp-for-who-grid { gap: 20px; }
}
</style>
