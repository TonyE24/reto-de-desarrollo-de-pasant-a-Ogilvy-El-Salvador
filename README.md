# Plataforma de Inteligencia de Mercados - Reto Ogilvy

MVP de plataforma de análisis de mercado para PYMEs que consolida múltiples fuentes de datos y genera insights accionables mediante inteligencia artificial.

**Desarrollado como parte del reto de pasantía de Ogilvy El Salvador 2026**

---

## 🚀 Stack Tecnológico

### Frontend
- **Framework:** React 18 + TypeScript
- **Build Tool:** Vite 5
- **Styling:** Tailwind CSS 3
- **Visualizaciones:** Recharts
- **Routing:** React Router v6
- **HTTP Client:** Axios

### Backend
- **Framework:** Laravel 11
- **Lenguaje:** PHP 8.2+
- **Base de Datos:** MySQL 8.0
- **Autenticación:** Laravel Sanctum
- **API:** RESTful

### DevOps
- **Deploy Frontend:** Vercel
- **Deploy Backend:** Railway
- **Testing:** PHPUnit + Vitest
- **CI/CD:** GitHub Actions

---

## 📋 Módulos de Inteligencia

1. **🏪 Inteligencia de Mercado**
   - Comparativa de precios con competidores
   - Análisis de cuota de mercado
   - Identificación de competidores principales

2. **📈 Inteligencia de Tendencias**
   - Análisis de keywords trending
   - Análisis de sentimiento (positivo/negativo/neutral)
   - Volumen de menciones temporales

3. **🔮 Inteligencia de Predicción**
   - Proyecciones de ventas con regresión lineal
   - Predicciones basadas en datos históricos
   - Métricas de confianza

4. **💡 Inteligencia de Innovación**
   - Detección de oportunidades de mercado
   - Identificación de gaps (vacíos) en el mercado
   - Tecnologías emergentes relevantes

---

## 🏗️ Estructura del Proyecto

```
reto-de-desarrollo-de-pasantia-Ogilvy-El-Salvador/
├── Backend/              # Laravel 11 API
│   ├── app/
│   │   ├── Http/Controllers/
│   │   ├── Models/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
├── Frontend/             # React + TypeScript + Vite
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── services/
│   │   └── types/
│   └── public/
└── docs/                 # Documentación completa
    ├── BUSINESS_ANALYSIS.md
    ├── TECHNICAL_DECISIONS.md
    ├── ARCHITECTURE.md
    ├── DATABASE_SCHEMA.md
    └── API_STRATEGY.md
```

---

## ⚡ Inicio Rápido

### Requisitos Previos
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0
- Git

### Backend (Laravel)
```bash
cd Backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```
**API disponible en:** http://localhost:8000

### Frontend (React)
```bash
cd Frontend
npm install
cp .env.example .env
npm run dev
```
**Aplicación disponible en:** http://localhost:5173

---

## 📚 Documentación Completa

### Documentos de Planificación (Semana 1)

| Documento | Descripción | Link |
|-----------|-------------|------|
| **Análisis de Negocio** | Requisitos funcionales y no funcionales | [BUSINESS_ANALYSIS.md](docs/BUSINESS_ANALYSIS.md) |
| **Decisiones Técnicas** | Stack tecnológico y justificaciones | [TECHNICAL_DECISIONS.md](docs/TECHNICAL_DECISIONS.md) |
| **Arquitectura** | Diagramas y flujos del sistema | [ARCHITECTURE.md](docs/ARCHITECTURE.md) |
| **Base de Datos** | Esquema ER y SQL completo | [DATABASE_SCHEMA.md](docs/DATABASE_SCHEMA.md) |
| **Estrategia de APIs** | Consumo de APIs externas y mocks | [API_STRATEGY.md](docs/API_STRATEGY.md) |

### Características Principales

✅ **Autenticación y Usuarios**
- Registro y login con email/password
- Recuperación de contraseña
- Roles: Admin y Usuario
- Tokens con Laravel Sanctum

✅ **Configuración de Empresa**
- Perfil de empresa personalizable
- Industria, país, región
- Palabras clave de interés

✅ **Dashboard Interactivo**
- KPIs principales
- Filtros por fecha y categoría
- Acceso rápido a módulos

✅ **Visualizaciones**
- Gráficos de líneas (tendencias)
- Gráficos de pastel (sentimiento)
- Gráficos de barras (comparativas)
- Tablas de datos

✅ **Seguridad**
- Contraseñas cifradas (bcrypt)
- Rutas protegidas
- Validación de inputs
- Prevención XSS/SQL Injection

---

## 👨‍💻 Desarrollo

### Cronograma (6 Semanas)

| Semana | Período | Objetivo | Estado |
|--------|---------|----------|--------|
| **1** | Feb 11-17 | Planificación y Arquitectura | ✅ Completado |
| **2** | Feb 18-24 | Autenticación y Estructura Base | 🔄 En progreso |
| **3** | Feb 25 - Mar 3 | Configuración y APIs | ⏳ Pendiente |
| **4** | Mar 4-10 | Dashboards y Visualizaciones | ⏳ Pendiente |
| **5** | Mar 11-17 | Procesamiento y Seguridad | ⏳ Pendiente |
| **6** | Mar 18-24 | Testing y Deploy | ⏳ Pendiente |

### Progreso Actual

**Semana 1 - Completada ✅**
- [x] Análisis del caso de negocio
- [x] Definición de stack tecnológico
- [x] Diseño de arquitectura del sistema
- [x] Diseño de base de datos
- [x] Estrategia de consumo de APIs
- [x] Setup de repositorios

**Próximos Pasos (Semana 2)**
- [ ] Implementar sistema de autenticación (backend)
- [ ] Crear páginas de login/register (frontend)
- [ ] Configurar Laravel Sanctum
- [ ] Integrar frontend con backend

---

## 🤝 Contribución

Este es un proyecto académico individual. Para sugerencias o feedback:

1. Crear un Issue describiendo la sugerencia
2. Fork del repositorio
3. Crear branch con feature (`git checkout -b feature/AmazingFeature`)
4. Commit de cambios (`git commit -m 'Add some AmazingFeature'`)
5. Push al branch (`git push origin feature/AmazingFeature`)
6. Abrir Pull Request

---

## 📊 Métricas del Proyecto

- **Líneas de código (estimado):** ~5,000
- **Endpoints API:** ~15
- **Componentes React:** ~30
- **Tablas de BD:** 8
- **Cobertura de tests (objetivo):** >70% backend, >60% frontend

---

## 🔗 Enlaces Útiles

- [Documentación de Laravel 11](https://laravel.com/docs/11.x)
- [Documentación de React](https://react.dev/)
- [Documentación de Tailwind CSS](https://tailwindcss.com/)
- [Documentación de Recharts](https://recharts.org/)

---

## 📄 Licencia

Proyecto académico desarrollado para el **Reto de Pasantía Ogilvy El Salvador 2026**

---

## 👤 Autor

**Desarrollador:** [Tu Nombre]  
**Institución:** [Tu Universidad]  
**Programa:** Pasantía Ogilvy El Salvador  
**Año:** 2026

---

**⭐ Si este proyecto te resulta útil, considera darle una estrella en GitHub!**
