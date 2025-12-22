# Project Vision: AI-Powered Dashboard Builder

## Codename: **PHOENIX**
### *The Mother of All Dashboard Builders*

---

## Executive Summary

PHOENIX is an AI-powered, low-code dashboard and application builder built on top of the micro-MVC framework. It enables AI assistants (Claude, GPT, etc.) to construct fully functional web applications through natural language commands by exposing a comprehensive set of building blocks via the Model Context Protocol (MCP).

**The Vision:** Tell an AI what you want, and watch it build a complete, production-ready application.

---

## Problem Statement

### Current Pain Points

1. **Low-code platforms are rigid** - Limited templates, proprietary, expensive
2. **Custom development is slow** - Every project starts from scratch
3. **AI coding assistants write code** - But don't understand YOUR system's patterns
4. **WordPress/WooCommerce extensions are limited** - Can't build truly custom experiences
5. **Technical debt accumulates** - Each developer does things differently

### The Gap

There's no system that:
- Lets AI understand and use YOUR building blocks
- Maintains consistency across all generated applications
- Integrates with existing data (WordPress, WooCommerce, custom DBs)
- Produces production-quality, maintainable code
- Can be extended with new templates and widgets

---

## Solution: PHOENIX

### Core Concept

PHOENIX provides a **structured vocabulary** for AI to build applications:

```
Instead of: "Write PHP code for a user dashboard"
We say:     "create_page('dashboard') + add_widget('user_stats')"
```

The AI doesn't write raw code—it **composes** from validated, tested building blocks.

### Key Components

```
┌─────────────────────────────────────────────────────────────┐
│                         PHOENIX                              │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   MCP       │  │  Template   │  │   Widget    │         │
│  │   Server    │  │   Engine    │  │   Library   │         │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘         │
│         │                │                │                 │
│         └────────────────┼────────────────┘                 │
│                          │                                  │
│                          ▼                                  │
│              ┌───────────────────────┐                      │
│              │     Code Generator    │                      │
│              └───────────┬───────────┘                      │
│                          │                                  │
│                          ▼                                  │
│              ┌───────────────────────┐                      │
│              │     micro-MVC Core    │                      │
│              └───────────────────────┘                      │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Target Users

### Primary Users

| User Type | Use Case |
|-----------|----------|
| **Developers** | Rapid prototyping, client projects, internal tools |
| **Agencies** | Quick client dashboards, white-label solutions |
| **Enterprises** | Custom internal applications, WooCommerce extensions |
| **Solo Founders** | MVP building, SaaS products |

### The AI User

The AI assistant becomes a **developer** that:
- Understands available templates and widgets
- Knows how to connect to databases
- Can scaffold entire applications
- Follows consistent patterns every time

---

## Unique Value Propositions

### 1. AI-Native Architecture
Built from ground up for AI interaction, not retrofitted.

### 2. WordPress/WooCommerce Integration
First-class support for extending WP/WC with custom applications.

### 3. Template + Widget Composition
Like LEGO blocks—AI picks and arranges, humans customize.

### 4. Production-Ready Output
Generated code is clean, maintainable, and follows best practices.

### 5. Extensible by Design
Add your own templates, widgets, and database connectors.

### 6. Self-Hosted / No Vendor Lock-in
Your code, your server, your data.

---

## Success Metrics

| Metric | Target |
|--------|--------|
| Time to build basic dashboard | < 5 minutes |
| Time to build CRUD application | < 15 minutes |
| Lines of code AI needs to write | 0 (composition only) |
| Template library size (v1) | 16 templates |
| Widget library size (v1) | 40+ widgets |
| Database connectors | MySQL, WordPress, WooCommerce |

---

## Project Phases

### Phase 1: Foundation (Current)
- [x] micro-MVC framework running
- [x] Basic dashboard with widgets
- [x] Media management system
- [x] Theme system
- [ ] Documentation complete

### Phase 2: Template Engine
- [ ] Template schema definition
- [ ] 16 core templates
- [ ] Template rendering engine
- [ ] Dynamic slot system

### Phase 3: Widget Library
- [ ] Widget schema definition
- [ ] 40+ core widgets
- [ ] Widget configuration system
- [ ] Data binding layer

### Phase 4: MCP Server
- [ ] MCP protocol implementation
- [ ] Tool definitions
- [ ] Authentication system
- [ ] Rate limiting / safety

### Phase 5: Database Layer
- [ ] MySQL connector
- [ ] WordPress connector
- [ ] WooCommerce connector
- [ ] Query builder with safety

### Phase 6: AI Integration
- [ ] Claude API integration
- [ ] OpenAI API integration
- [ ] Conversation context
- [ ] Build history / versioning

### Phase 7: Polish & Launch
- [ ] Admin UI for template management
- [ ] Marketplace for community templates
- [ ] Documentation site
- [ ] Example applications

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| **MCP Server** | Node.js + TypeScript |
| **Core Framework** | micro-MVC (PHP) |
| **Database** | MySQL (compatible with WordPress) |
| **Frontend** | Vanilla JS + CSS (no framework dependency) |
| **Template Format** | JSON Schema |
| **API Protocol** | MCP (Model Context Protocol) |

---

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| AI generates unsafe database queries | Query builder with whitelist, parameterized queries |
| Template conflicts | Namespacing, version control |
| Performance with many widgets | Lazy loading, caching |
| AI hallucinations | Strict tool schemas, validation |
| Scope creep | Phased approach, MVP first |

---

## Next Steps

1. **Finalize architecture documentation**
2. **Define MCP tool specifications**
3. **Design template schema format**
4. **Build proof-of-concept MCP server**
5. **Create first 4 templates as validation**

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2025-12-21 | Claude + Human | Initial vision document |

---

*"The best way to predict the future is to build it."*
