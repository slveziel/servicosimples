<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServicoSimples - Controle de Ordens de Serviço para MEIs</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #1a1a2e; line-height: 1.6; background: #fff; }
        
        /* Header */
        header { padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; position: fixed; width: 100%; top: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); z-index: 1000; box-shadow: 0 2px 20px rgba(0,0,0,0.05); }
        .logo { font-size: 24px; font-weight: 700; color: #2563eb; }
        .logo span { color: #1a1a2e; }
        nav { display: flex; gap: 30px; align-items: center; }
        nav a { text-decoration: none; color: #4b5563; font-weight: 500; transition: color 0.3s; }
        nav a:hover { color: #2563eb; }
        .btn { padding: 12px 28px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.3s; text-decoration: none; display: inline-block; border: none; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-2px); box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3); }
        .btn-outline { background: transparent; border: 2px solid #e5e7eb; color: #1a1a2e; }
        .btn-outline:hover { border-color: #2563eb; color: #2563eb; }
        
        /* Hero */
        .hero { padding: 160px 40px 100px; max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .hero-content h1 { font-size: 52px; line-height: 1.2; margin-bottom: 24px; color: #1a1a2e; }
        .hero-content h1 span { color: #2563eb; }
        .hero-content p { font-size: 20px; color: #6b7280; margin-bottom: 32px; }
        .hero-buttons { display: flex; gap: 16px; }
        .hero-image { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 24px; padding: 40px; color: white; box-shadow: 0 30px 60px rgba(37, 99, 235, 0.2); }
        .mockup { background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .mockup-header { display: flex; gap: 8px; margin-bottom: 16px; }
        .mockup-dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }
        .mockup-content { height: 80px; background: #f3f4f6; border-radius: 8px; }
        
        /* Features */
        .features { padding: 100px 40px; background: #f9fafb; }
        .section-header { text-align: center; max-width: 600px; margin: 0 auto 60px; }
        .section-header h2 { font-size: 40px; margin-bottom: 16px; }
        .section-header p { color: #6b7280; font-size: 18px; }
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px; max-width: 1200px; margin: 0 auto; }
        .feature-card { background: white; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-8px); }
        .feature-icon { width: 60px; height: 60px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px; }
        .feature-card h3 { font-size: 20px; margin-bottom: 12px; }
        .feature-card p { color: #6b7280; }
        
        /* How it works */
        .how-it-works { padding: 100px 40px; }
        .steps { display: flex; justify-content: center; gap: 40px; max-width: 1000px; margin: 0 auto; }
        .step { text-align: center; flex: 1; }
        .step-number { width: 80px; height: 80px; background: #2563eb; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; margin: 0 auto 20px; }
        .step h3 { font-size: 22px; margin-bottom: 12px; }
        .step p { color: #6b7280; }
        
        /* Pricing */
        .pricing { padding: 100px 40px; background: #1a1a2e; color: white; }
        .pricing .section-header h2 { color: white; }
        .pricing .section-header p { color: #9ca3af; }
        .pricing-card { background: white; color: #1a1a2e; max-width: 500px; margin: 0 auto; padding: 48px; border-radius: 24px; text-align: center; box-shadow: 0 30px 60px rgba(0,0,0,0.3); }
        .pricing-card h3 { font-size: 28px; margin-bottom: 8px; }
        .pricing-card .price { font-size: 64px; font-weight: 700; color: #2563eb; margin: 24px 0; }
        .pricing-card .price span { font-size: 20px; color: #6b7280; font-weight: 400; }
        .pricing-features { list-style: none; margin: 32px 0; text-align: left; }
        .pricing-features li { padding: 12px 0; border-bottom: 1px solid #f3f4f6; display: flex; align-items: center; gap: 12px; }
        .pricing-features li::before { content: "✓"; color: #10b981; font-weight: 700; }
        .pricing-btn { width: 100%; padding: 16px; font-size: 18px; }
        .pricing-note { margin-top: 20px; color: #6b7280; font-size: 14px; }
        
        /* CTA */
        .cta { padding: 100px 40px; text-align: center; }
        .cta h2 { font-size: 40px; margin-bottom: 16px; }
        .cta p { color: #6b7280; font-size: 20px; margin-bottom: 32px; }
        
        /* Footer */
        footer { padding: 40px; text-align: center; color: #6b7280; border-top: 1px solid #e5e7eb; }
        footer a { color: #2563eb; text-decoration: none; }
        
        /* Responsive */
        @media (max-width: 968px) {
            .hero { grid-template-columns: 1fr; text-align: center; }
            .hero-buttons { justify-content: center; }
            .features-grid { grid-template-columns: 1fr; }
            .steps { flex-direction: column; }
            header { padding: 20px; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">🔧 Servico<span>Simples</span></div>
        <nav>
            <a href="#recursos">Recursos</a>
            <a href="#como-funciona">Como Funciona</a>
            <a href="#precos">Preços</a>
            <a href="/app">Entrar</a>
            <a href="/app" class="btn btn-primary">Começar Grátis</a>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h1>Controle suas <span>Ordens de Serviço</span> de forma simples</h1>
            <p>Perfeito para eletricistas, técnicos de informática, encanadores e prestadores de serviço MEI. Organize seus clientes, acompanhe seus serviços e nunca mais perca um pagamento.</p>
            <div class="hero-buttons">
                <a href="/app" class="btn btn-primary">Começar Agora →</a>
                <a href="#recursos" class="btn btn-outline">Ver Recursos</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="mockup">
                <div class="mockup-header">
                    <div class="mockup-dot dot-red"></div>
                    <div class="mockup-dot dot-yellow"></div>
                    <div class="mockup-dot dot-green"></div>
                </div>
                <div class="mockup-content"></div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div class="mockup" style="padding: 16px;">
                    <div style="font-size: 12px; color: #6b7280;">Clientes</div>
                    <div style="font-weight: 600;">24 cadastrados</div>
                </div>
                <div class="mockup" style="padding: 16px;">
                    <div style="font-size: 12px; color: #6b7280;">OS Este Mês</div>
                    <div style="font-weight: 600;">18 realizadas</div>
                </div>
                <div class="mockup" style="padding: 16px; grid-column: span 2;">
                    <div style="font-size: 12px; color: #6b7280;">Faturamento</div>
                    <div style="font-weight: 600; color: #10b981;">R$ 4.580,00</div>
                </div>
            </div>
        </div>
    </section>

    <section class="features" id="recursos">
        <div class="section-header">
            <h2>tudo que você precisa</h2>
            <p>Ferramentas simples e práticas para organizar seu negócio sem complicação</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📋</div>
                <h3>Cadastro de Clientes</h3>
                <p>Guarde nome, telefone, email e endereço de todos os seus clientes em um só lugar. Nunca mais perca uma informação importante.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔧</div>
                <h3>Ordens de Serviço</h3>
                <p>Crie, acompanhe e finalize OS rapidamente. Defina status como Pendente, Concluído ou Pago para manter tudo organizado.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Controle Financeiro</h3>
                <p>Veja quanto você fatura por mês e no total. Organize seus ganhos de forma clara e simples.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Dashboard Completo</h3>
                <p>Tenha uma visão geral do seu negócio: total de OS, por status e valores em aberto. Tudo em uma tela.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Acesse de Qualquer Lugar</h3>
                <p>Use no celular, tablet ou computador. Tudo funciona na nuvem, sem precisar instalar nada.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>100% Seguro</h3>
                <p>Seus dados são seus. Cada usuário vê apenas as próprias informações, com total privacidade.</p>
            </div>
        </div>
    </section>

    <section class="how-it-works" id="como-funciona">
        <div class="section-header">
            <h2>como funciona</h2>
            <p>Comece a usar em menos de 2 minutos</p>
        </div>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Cadastre-se</h3>
                <p>Informe seu nome, empresa e email. É Grátis e rápido.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Adicione Clientes</h3>
                <p>Comece a cadastrar seus clientes com todas as informações.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Crie Ordens de Serviço</h3>
                <p>Gere OS para cada serviço prestado e acompanhe os status.</p>
            </div>
        </div>
    </section>

    <section class="pricing" id="precos">
        <div class="section-header">
            <h2>preço justo</h2>
            <p>Sem taxas escondidas, sem surpresas. Valor único e acessível.</p>
        </div>
        <div class="pricing-card">
            <h3>Plano Único</h3>
            <p>Tudo incluso, sem limitações</p>
            <div class="price">R$ 19<span>,00/mês</span></div>
            <ul class="pricing-features">
                <li>Cadastro ilimitado de clientes</li>
                <li>Ordens de serviço ilimitadas</li>
                <li>Dashboard completo</li>
                <li>Controle financeiro</li>
                <li>Acesso em qualquer dispositivo</li>
                <li>Suporte por email</li>
                <li>Sem taxas de setup</li>
                <li>Cancele quando quiser</li>
            </ul>
            <a href="/app" class="btn btn-primary pricing-btn">Começar Agora</a>
            <p class="pricing-note">🎉 Primeiro mês GRÁTIS para novos usuários!</p>
        </div>
    </section>

    <section class="cta">
        <h2>Pronto para organizar seu negócio?</h2>
        <p>Junte-se a centenas de prestadores de serviço que já usam o ServicoSimples</p>
        <a href="/app" class="btn btn-primary">Criar Minha Conta Grátis →</a>
    </section>

    <footer>
        <p>© 2026 ServicoSimples. Feito com 💙 para prestadores de serviço MEI.</p>
        <p style="margin-top: 8px;">
            <a href="#">Termos de Uso</a> · 
            <a href="#">Privacidade</a> · 
            <a href="#">Contato</a>
        </p>
    </footer>
</body>
</html>
