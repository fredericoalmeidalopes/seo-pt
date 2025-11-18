# 🇵🇹 SEO-PT — Add-on de SEO para WordPress optimizado para Portugal

O **SEO-PT** é um add-on complementar aos maiores plugins SEO do mercado — **Yoast**, **Rank Math**, **SEOPress**, **AIOSEO** — desenvolvido especificamente para resolver duas limitações que estes plugins **não conseguem cobrir por serem genéricos e internacionais**:

---

## ✅ O que este plugin resolve (as 2 grandes falhas dos plugins de SEO globais)

### 1️⃣ Curadoria linguística para Português Europeu (PT-PT)
A maioria das ferramentas de SEO assume o **português do Brasil** como padrão.  
O SEO-PT corrige isso ao introduzir:

- Conversão automática BR → PT-PT (dicionário customizável)  
- Alertas linguísticos:  
  - gerúndio excessivo (*“estamos realizando” → “estamos a realizar”*)  
  - termos não naturais em português europeu  
  - construções sintáticas pouco elegantes  
- Sugestões automáticas de estilo  
- Análise textual integrada no painel WordPress

 **Resultado:** conteúdos mais naturais para o público português, maior relevância semântica e melhoria do SEO on-page.

---

### 2️⃣ Schema e localização adaptados à organização territorial única de Portugal
Nenhum plugin SEO internacional conhece a divisão geográfica portuguesa.

O SEO-PT inclui uma **base de dados oficial de:**

- **18 distritos**
- **308 concelhos**
- **3092 freguesias**
- + localidades (quando aplicável)

E permite:

- Associar cada página/negócio/local a distrito, concelho e freguesia  
- Gerar schema LocalBusiness **100% compatível com o território português**  
- Corrigir automaticamente marcações que Yoast/RankMath fazem de forma imprecisa  
- Exportar / importar a base de dados em JSON

 **Resultado:** dados estruturados mais completos e mais relevantes para negócios locais portugueses.

---

#  Compatibilidade

O SEO-PT funciona como **extensão (add-on) complementar** aos seguintes plugins:

| Plugin SEO | Compatibilidade | Observações |
|-----------|-----------------|-------------|
| **Yoast SEO** | ✔️ | Funciona em paralelo, adicionando camadas PT-PT e schema PT |
| **Rank Math** | ✔️ | Melhora linguagem e localização sem conflitos |
| **AIOSEO** | ✔️ | Mantém-se como add-on independente |
| **SEOPress** | ✔️ | Complementar e não intrusivo |

O SEO-PT **não substitui** o teu plugin SEO.  
Ele **completa aquilo que nenhum plugin internacional cobre.**

---

#  Funcionalidades principais

###  1. Curadoria linguística PT-PT
- Conversão automática BR → PT-PT  
- Dicionário editável em JSON  
- Alertas de vocabulário (ex.: *ônibus → autocarro*)  
- Alertas de estilo:  
  - gerúndios  
  - redundâncias  
  - sintaxe pouco fluida  
- Correção em massa (em desenvolvimento)

###  2. Localização e esquema PT
- Base de dados completa de distritos / concelhos / freguesias  
- Atributos específicos para LocalBusiness  
- Geração de schema otimizado para Portugal  
- Campo específico para localização no editor WordPress  
- API interna que permite outros plugins usarem esta informação

###  3. Painel de controlo simples
- Abas “Geral” e “Localização”  
- Guardar configurações via API Settings WordPress  
- Interface leve e rápida  

###  4. Arquitetura modular
- Código dividido por módulos (linguagem, localização, UI)  
- Fácil de estender ou integrar noutros plugins  
- JSONs independentes para atualização contínua

---

#  Instalação

### Método 1: via WordPress
1. Aceder a **Plugins → Adicionar novo**
2. Carregar o ficheiro `.zip`
3. Instalar e ativar

### Método 2: via FTP
1. Extrair o `.zip`
2. Enviar a pasta para `/wp-content/plugins/seo-pt/`
3. Ativar no painel WordPress

---

#  Como usar

##  Passo 1 — Ativar a curadoria linguística PT-PT
1. Ir a **SEO-PT → Geral**  
2. Ativar:
   - Conversão BR → PT-PT  
   - Sugestões de estilo  
   - Alertas de gerúndios  
3. Guardar

### Exemplo
Texto:  
> “Estamos realizando uma atualização para melhorar a performance.”

Alerta gerado:  
> “Sugestão PT-PT: *Estamos a realizar uma atualização…*”

---

##  Passo 2 — Definir localização PT para schema
1. Ir a **SEO-PT → Localização**  
2. Selecionar:
   - Distrito  
   - Concelho  
   - Freguesia  
3. Opcional: definir **localidade**  
4. O plugin passa a gerar schema PT-optimizado em todas as páginas de LocalBusiness.

### Exemplo de schema gerado
```json
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Exemplo Lda",
  "address": {
    "addressCountry": "PT",
    "addressRegion": "Lisboa",
    "addressLocality": "Cascais",
    "areaServed": "Cascais",
    "district": "Lisboa",
    "municipality": "Cascais",
    "parish": "Alcabideche"
  }
}
