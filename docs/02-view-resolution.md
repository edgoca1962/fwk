# WP FRW — Resolución declarativa de vistas

## Propósito

La resolución declarativa permite que los módulos definan su presentación
mediante archivos de configuración, evitando grandes bloques condicionales.

## Flujo

RequestContext
→ ModuleRegistry
→ ModuleInterface::configure_view()
→ AbstractModule::resolve_view_config()
→ ViewResolver
→ config/view.php
→ ViewContext
→ index.php

## Capas de configuración

1. Defaults globales de Core.
2. Defaults del módulo.
3. Tipo general de solicitud.
4. Página específica.
5. Post type.
6. Singular, archivo, búsqueda o listado.
7. Taxonomía y término.
8. Reglas especiales.
9. Filtros finales.

## Estructura de configuración

- defaults
- front_page
- home
- page
- singular
- archive
- post_type_archive
- taxonomy
- search
- 404
- pages
- post_types
- taxonomies
- rules

## Regla arquitectónica

La lógica visual debe expresarse preferentemente mediante configuración.

Los métodos PHP se reservan para condiciones que no puedan describirse
de manera clara mediante configuración declarativa.


## Manifest y configuración visual

Los módulos declaran el recurso de vistas en su `ModuleManifest`. `AbstractModule`
obtiene desde allí la configuración correspondiente, evitando resolver rutas
mediante reflexión o conocimiento externo de la estructura del módulo.

`ModuleInterface` conserva el punto de extensión:

```php
public function configure_view(
    ViewContext $view,
    RequestContext $request
): void;
```

La implementación común puede resolverse declarativamente y cada módulo puede
añadir comportamiento dinámico cuando sea realmente necesario.

## Cascada e historial

`ViewContext` conserva el origen de sus modificaciones. Entre las fuentes que
pueden aparecer en el historial están:

```text
defaults
core:request
core:module
module:sgf:defaults
module:sgf:page:tablero
```

La finalidad es conocer tanto el valor final como qué capa lo produjo.

## Un módulo puede administrar varios CPT

Un módulo no equivale a un único post type.

SGF, por ejemplo, administra:

```text
SGF
├── billetera
├── libro
├── banco
└── presupuesto
```

La clase del módulo coordina las condiciones comunes y cada CPT puede
especializar la vista sin trasladar esa lógica al Core.

## Páginas funcionales

Se prefieren slugs distintos para páginas que representan funciones diferentes,
evitando ambigüedad entre tableros u otras vistas funcionales.

El Core puede administrar las páginas genéricamente y el módulo puede modificar
su forma y contenido mediante su configuración.

## SGF: títulos contextuales por billetera

Los movimientos de `libro` y `banco` utilizarán `post_parent` para identificar
la billetera asociada.

Una vista puede construir dinámicamente títulos como:

```text
Movimientos Contables Cuenta Colones
```

donde `Cuenta Colones` corresponde al `post_title` de la billetera padre.

El título compuesto debe resolverse en presentación y no duplicarse como dato
persistido. Si cambia el nombre de la billetera, la vista debe reflejar el
cambio automáticamente.

La obtención y descripción funcional de la billetera pertenece al módulo SGF y
podrá encapsularse posteriormente en un servicio específico.

## SGF: Rubros

La taxonomía técnica utilizada por SGF es:

```text
sgf_igt
```

y su nombre visible es `Rubro / Rubros`.

`RequestContext` y la configuración declarativa pueden resolver la presentación
de sus archivos y términos. La propiedad de los términos por usuario es lógica
funcional de SGF y no debe incorporarse a `ViewResolver`.

## Regla de responsabilidad

La configuración declarativa debe resolver estructura y presentación
predecibles.

El código PHP debe reservarse para comportamiento verdaderamente dinámico.

El patrón objetivo continúa siendo:

```text
Core
    define

Módulo
    especializa

Página / CPT / Taxonomía
    especializa

Contexto dinámico
    completa

index.php
    renderiza
```
