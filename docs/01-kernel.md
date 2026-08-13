# WP FRW — Kernel

## Propósito

El Kernel inicializa la infraestructura global del framework y coordina
el registro de módulos.

## Flujo de inicialización

functions.php
→ Core::get_instance()
→ WPSetup::get_instance()
→ Core::register_modules()
→ ModuleRegistry::boot()
→ DebugInspector::get_instance()

## Componentes

### Core

Coordina la inicialización y resuelve el contexto visual.

Core no debe contener lógica específica de módulos como Post o SGF.

### Singleton

Garantiza una instancia única por clase durante cada solicitud.

Debe utilizarse en servicios y módulos globales, no en objetos de datos.

### ModuleInterface

Define el contrato que debe cumplir cualquier módulo.

### AbstractModule

Proporciona inicialización idempotente y resolución básica por página,
post type o módulo solicitado.

### ModuleRegistry

Almacena, inicializa y localiza los módulos activos.

### RequestContext

Representa qué está ocurriendo en la solicitud actual.

### ViewContext

Representa cómo debe presentarse la solicitud.

### DebugInspector

Expone el estado interno del framework únicamente durante desarrollo.

## Reglas arquitectónicas

1. Core no conoce los post types particulares de SGF.
2. Un módulo puede administrar uno o varios post types.
3. Un post type tiene un único módulo propietario.
4. Las taxonomías podrán compartirse entre múltiples post types.
5. Los módulos y servicios globales pueden usar Singleton.
6. Los contextos y objetos de datos no deben usar Singleton.
7. Los templates permanecen separados de las clases estructurales.


## Arquitectura declarativa de módulos

`ModuleManifest` es la fuente de verdad declarativa de cada módulo. Puede
declarar páginas, post types, taxonomías, vistas y, progresivamente, otros
recursos.

Flujo general:

```text
ModuleManifest
├── pages
├── post_types
├── taxonomies
└── views
```

`ModuleRegistry` valida módulos, dependencias, conflictos y orden de arranque,
y entrega los recursos a los Registries especializados.

## Post Types

La infraestructura actual utiliza:

```text
PostTypeDefinition
→ PostTypeRegistry
→ init (prioridad 5)
→ register_post_type()
```

`PostTypeDefinition` normaliza y valida las declaraciones. `PostTypeRegistry`
las materializa en WordPress, detecta duplicados y conserva información de
diagnóstico.

Los CPT pueden declararse con `enabled => false` para mantener el recurso en el
Manifest sin registrarlo todavía.

### SGF

Los CPT previstos son:

```text
billetera
libro
banco
presupuesto
```

El CPT técnico `libro`, con plural visible `Libros`, representa movimientos
contabilizados.

## Taxonomías

La infraestructura actual utiliza:

```text
TaxonomyDefinition
→ TaxonomyRegistry
→ init (prioridad 10)
→ register_taxonomy()
```

Una taxonomía se define una sola vez y puede asociarse a uno o varios post
types.

### Rubros de SGF

La taxonomía técnica es:

```text
sgf_igt
```

Su significado es:

```text
Sistema de Gestión Financiera
Ingresos - Gastos - Transferencias
```

Su nombre visible es:

```text
Rubro / Rubros
```

Es jerárquica.

No se crearán taxonomías distintas por usuario. Se registra una sola
`sgf_igt`; posteriormente, los términos de cada usuario se identificarán
mediante `term_meta`, usando un identificador de propietario como `user_id`.

El aislamiento por usuario deberá aplicarse en lectura, creación, modificación,
eliminación, REST y AJAX. Esa lógica pertenece al módulo SGF, no al Kernel.

El sistema podrá proporcionar una estructura genérica inicial de Rubros y
copiarla para cada usuario para que pueda adaptarla sin afectar a otros.

## Relación entre movimientos y billeteras

Los movimientos de `libro` y `banco` estarán asociados a una billetera mediante
el campo nativo de WordPress:

```php
post_parent
```

Modelo:

```text
billetera
    │
    ├── libro
    │     post_parent = billetera.ID
    │
    └── banco
          post_parent = billetera.ID
```

Esta relación no debe duplicarse mediante post meta.

Los títulos contextuales de las vistas pueden construirse dinámicamente usando
el `post_title` de la billetera. Por ejemplo:

```text
Movimientos Contables Cuenta Colones
```

donde `Cuenta Colones` es el título de la billetera asociada. El texto
compuesto es presentación y no debe persistirse innecesariamente.

## Inspector

El Inspector se utiliza como herramienta de diagnóstico del framework. Además
de Core, módulos, RequestContext y ViewContext, puede exponer los estados de
PostTypeRegistry y TaxonomyRegistry, incluyendo errores y advertencias.

Principio de desarrollo:

> Los componentes estructurales importantes deben ser observables mediante el
> Inspector cuando esa observabilidad facilite su validación.

## Rewrites

Durante desarrollo, cuando se incorporan o modifican CPT o taxonomías, las
rewrite rules se regeneran manualmente mediante:

```text
Ajustes → Enlaces permanentes → Guardar cambios
```

No se debe ejecutar `flush_rewrite_rules()` en cada solicitud.

## Estado actual y próximos componentes

Implementado y probado:

```text
Core
Singleton
WPSetup
ModuleInterface
AbstractModule
ModuleManifest
ModuleRegistry
RequestContext
ViewContext
ViewResolver
DebugInspector
PostTypeDefinition
PostTypeRegistry
TaxonomyDefinition
TaxonomyRegistry
```

Siguiente etapa prevista:

```text
MetaDefinition
MetaRegistry
CapabilityBuilder
```

Al diseñar metadata debe respetarse la decisión de que la relación
movimiento-billetera utiliza `post_parent`.
