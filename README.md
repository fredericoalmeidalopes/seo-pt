# SEO PT — Plugin WordPress

O **SEO PT** é um plugin desenvolvido para auxiliar na otimização de conteúdos em Português (PT-PT e PT-BR), oferecendo ferramentas de revisão linguística, boas práticas de SEO e funcionalidades de localização para sites WordPress.

---

##  Funcionalidades Principais

###  Conversão PT-BR ⇄ PT-PT  
- Detecção automática de vocabulário brasileiro.  
- Sugestões de substituição para português europeu.  
- Sistema baseado em dicionários personalizados.  

###  Revisão de Sintaxe e Estilo  
- Identificação de uso excessivo de gerúndios.  
- Alerta para construções fracas, redundâncias ou expressões pouco naturais.  
- Sugestões de melhoria baseadas em padrões linguísticos.

###  Módulo de Localização  
- Gestão de distritos, concelhos e localidades portuguesas.  
- Base de dados PT estruturada em JSON.  
- Preparado para integração com campos de contacto ou formulários.

###  Interface simples no WordPress  
- Painel dedicado no menu lateral do WP.  
- Tabs: Geral / Localização (JS e CSS próprios).  
- Opções gravadas via API nativa (`settings_api`).

---

##  Estrutura do Plugin

```
seo-pt/
│
├── pt-seo.php                 # Ficheiro principal do plugin
│
├── assets/
│   ├── admin.js               # Scripts gerais do painel
│   └── admin.css              # Estilos gerais
│
├── modules/
│   ├── lingua/                # Módulo linguístico
│   │   ├── module-lingua.php
│   │   ├── lingua-unified.js
│   │   ├── lingua.css
│   │   └── dictionary/
│   │        ├── patterns.json
│   │        └── ptbr-ptpt.json
│   │
│   └── local/                 # Módulo de localização
│       ├── module-local.php
│       ├── admin-local.css
│       ├── admin-local.js
│       └── data/
│            └── pt-admin.min.json
│
└── includes/
    ├── class-options.php
    └── class-admin.php
```

---

##  Instalação

1. Carregar a pasta do plugin para:  
   `wp-content/plugins/seo-pt/`
2. Aceder ao painel do WordPress.  
3. Ir a **Plugins → Ativar**.  

---

##  Desenvolvimento

### Requisitos
- PHP 7.4+
- WordPress 5.8+
- Conhecimentos básicos de hooks, actions e filters (opcional)

### Notas de desenvolvimento
- Cada módulo é independente.  
- JS modularizado por contexto (linguística vs. localização).  
- JSONs externos permitem futuras atualizações sem alterar código PHP.

---

##  Roadmap (futuras melhorias)

- Integração com Gutenberg (blocos de aviso/alerta).  
- Ferramenta de análise por página (score SEO + linguística).  
- Exportação / importação de dicionários personalizados.  
- Modo automático de correção em massa.  

---

##  Licença
MIT License

---

##  Contacto
Frederico Lopes – info@fredericolopes.com
Website: https://fredericolopes.com
