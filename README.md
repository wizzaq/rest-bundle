Wizzaq Rest Bundle
==================

Rest bundle what you always looked for.

Usage
-----

* [ProcessForm argument](#processform-argument)
* [Protocols](#protocols)

### ProcessForm argument

Wraps typical form handling to more clean code of action.

#### Configuration

```yaml
# config/packages/wizzaq_rest.yaml
wizzaq_rest:
   use_resolvers: true # set to false to completely disable resolvers
```

#### Simple example

Simple example of typical controller.

```php
<?php

namespace App\Controller;

use App\Form\Filter\MyEntityFilterType;
use App\Form\Dto\Filter\MyEntityFilter;
use App\Repository\MyEntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wizzaq\RestBundle\Attribute\ProcessForm;

class MyEntityController extends AbstractController
{
   #[Route('/my-entity', methods: 'GET')]
   public function list(
       #[ProcessForm(MyEntityFilterType::class, mapEntity: false, throwOnNotValid: false)] ?MyEntityFilter $filter = null,
       MyEntityRepository $repository
   ): Response {
       return $this->json(['list' => $repository->list($filter ?? new MyEntityFilter())]);
   }

   #[Route('/my-entity', methods: 'POST')]
   #[Route('/my-entity/{id}', methods: 'PUT')]
   public function edit(
       #[ProcessForm(MyEntityType::class)] MyEntity $entity,
       EntityManagerInterface $em
   ): Response {
       // $entity is processed by form and validated
       // Wizzaq\RestBundle\Exception\FormValidationException will be thrown if validation failed
       $em->persist($entity);
       $em->flush();

       return new JsonResponse(['id' => $entity->getId()]);
   }

}
```

#### All options

A number of options are available on the ``ProcessForm`` attribute to
control behavior:

> ``form``: `string` (required)
>
>  Form type class name to process
>
> ```php
> #[Route('/my-entity/{id}', methods: 'PUT')]
> public function edit(
>    #[ProcessForm(form: MyEntityType::class)] MyEntity $entity,
>    EntityManagerInterface $em
> ): Response {
>    // $entity is processed by form and validated
>    // Wizzaq\RestBundle\Exception\FormValidationException will be thrown if validation failed
>    $em->persist($entity);
>    $em->flush();
>
>    return new JsonResponse(['id' => $entity->getId()]);
> }
> ```
> ---
> ``mapEntity``: `null`|`bool` (default: `null`)
> 
> If not null, then strictly notify `ProcessFormResolver` use or not `EntityValueResolver` to resolve value before process by form.  
> If `false`, then `null` will be used as initial value to create form.  
> If `null`, then `ProcessFormResolver` will try to guess it. Can be useful if action supports create and edit at once.
> 
> :warning: We recommend you always specify this option explicitly.
>
> ```php
> #[Route('/my-entity/{id}', methods: 'PUT')]
> public function edit(
>    #[ProcessForm(form: MyEntityType::class, mapEntity: true)] MyEntity $entity,
>    EntityManagerInterface $em
> ): Response {
>    // $entity is processed by form and validated
>    // Wizzaq\RestBundle\Exception\FormValidationException will be thrown if validation failed
>    $em->persist($entity);
>    $em->flush();
>
>    return new JsonResponse(['id' => $entity->getId()]);
> }
> ```
> 
> ---
> ``submit``: `bool` (default `false`)
>
> If true, then `$form->submit($data, false)` (yes, with `$clearMissing` = `false`) will we used instead of default
> `$form->handleRequest($request)`
>
> ```php
> #[Route('/my-entity', methods: 'GET')]
> public function list(
>     #[ProcessForm(MyEntityFilterType::class, submit: true)] ?MyEntityFilter $filter = null,
>     MyEntityRepository $repository
> ): Response {
>     return $this->json(['list' => $repository->list($filter ?? new MyEntityFilter())]);
> }
> ```
> ---
> ``throwOnNotValid``: `bool` (default `true`)
>
> By default `ProcessFormResolver` throwing `FormValidationException` if validation failing.
>
> Set `throwOnNotValid` to `false` if you want to process not valid form inside action.
>
> Processed form available by
> ```php
> \Wizzaq\RestBundle\Config\RestConfig::processedForm($request)
> ```
>
> ```php
> ...
> use Wizzaq\RestBundle\Config\RestConfig;
> ...
>
> #[Route('/my-entity/{id}', methods: ['GET', 'POST'])]
> public function edit(
>    #[ProcessForm(form: MyEntityType::class, throwOnNotValid: false)] ?MyEntity $entity = null,
>    Request $request,
>    RestConfig $restConfig,
>    EntityManagerInterface $em
> ): Response {
>    if (null === $entity) {
>        $this->addFlash(
>            'error',
>            'Entity not found!'
>        );
>
>        return new RedirectResponse('/my-entity');
>    }
>
>    $form = $restConfig->processedForm($request);
>
>    if ($form->isSubmitted() && $form->isValid()) {
>        // do some stuff here
>        $em->persist($entity);
>        $em->flush();
>
>        return new RedirectResponse('/my-entity');
>    }
>
>    return $this->render('my_entity/edit.html.twig', [
>        'form' => $form,
>    ]);
> }
>
> ```
>
> ---
> ##### Inherited from [MapEntity](https://symfony.com/doc/current/doctrine.html#mapentity-options):
>
> ``id``
>
> If an `id` option is configured and matches a route parameter, then the resolver will find by the primary key
>
> ``mapping``
>
> Configures the properties and values to use with the `findOneBy()` method: the key is the route placeholder name and the value is the Doctrine property name
>
> ``exclude``
>
> Configures the properties that should be used in the `findOneBy()` method by _excluding_ one or more properties so that not _all_ are used
>
> ``stripNull``
>
> If true, then when `findOneBy()` is used, any values that are `null` will not be used for the query.
>
> ``objectManager``
>
> By default, the `EntityValueResolver` uses the default object manager, but you can configure this
>
> ``evictCache``
>
> If true, forces Doctrine to always fetch the entity from the database instead of cache.
>
> ``disabled``
>
> If true, the `ProcessFormResolver` will not try to replace the argument.
>
> ``resolver``
>
> By default `ProcessFormResolver` will be used to resolve argument, but you can configure this

#### How it works.

`ProcessForm` argument extends [MapEntity](https://symfony.com/doc/current/doctrine.html#mapentity-options) and using `EntityValueResolver` to find existing entity exactly as described in their doc.

After `ProcessFormResolver` creating form with parameters:
- with object (or `null`) returned from `EntityValueResolver` as `$data`
- current `method` of form will be replaced with actual method from `$request`, if method is not `GET`

and processing it according defined options.

If form valid it returns processed data from form.

if not, then it throws `FormValidationException` or returns found object if `throwOnNotValid` is `false`

### Protocols

While developing an API we all have main listener to decode payload from JSON and put it back into request to process like:

```php
public function onRequest(RequestEvent $event): void
{
    $request = $event->getRequest();

    if ('' === $request->getContent()) {
        return;
    }

    if ('json' !== $request->getContentTypeFormat()) {
        return;
    }

    try {
        $request->request->replace($request->toArray());
    } catch (JsonException $e) {
        throw new BadRequestHttpException('Unable to parse request.', $e);
    }
}
```

_or you decoding it every time in actions_ :thinking:

Now you can use `WizzaqRestBundle` to automate it by two ways:

> :warning: Protocols handling enabled by [default config](#configuration-options)

1. Simple add `_rest: true` to route default options in `config/routes.yaml`:
   ```yaml
   controllers:
     resource: ../src/Controller/Api/
     type: attribute
     prefix: /api
     defaults: { _rest: true }
   ```

2. Use `Rest` argument. Can be applied to class or method:
   ```php
    <?php

    namespace App\Controller;

    use App\Form\Filter\MyEntityFilterType;
    use App\Form\Dto\Filter\MyEntityFilter;
    use App\Repository\MyEntityRepository;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Bridge\Twig\Attribute\Template;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
    use Symfony\Component\Routing\Attribute\Route;
    use Wizzaq\RestBundle\Attribute\ProcessForm;
    use Wizzaq\RestBundle\Attribute\Rest;
    use Wizzaq\RestBundle\Exception\FormValidationException;

    class MyEntityController extends AbstractController
    {
        #[Route('/my-entity', methods: 'GET')]
        #[Rest(responseSerializationGroups: 'default')]
        public function list(
            #[ProcessForm(form: MyEntityFilterType::class, throwOnNotValid: false)] ?MyEntityFilter $filter = null,
            MyEntityRepository $repository
        ): array {
            return ['list' => $repository->list($filter ?? new MyEntityFilter())];
        }

        #[Route('/my-entity/{id}', name: 'app.my_entity.get_or_update', methods: ['GET', 'POST'])]
        #[Route('/api/my-entity/{id}', name: 'api.my_entity.get_or_update', methods: ['GET', 'POST'])]
        #[Rest(routes: 'api.my_entity.get_or_update', responseSection: 'entity', responseSerializationGroups: ['with_related', 'default'])]
        #[Template('my_entity/edit.html.twig')]
        public function edit(
            #[ProcessForm(MyEntityType::class, throwOnNotValid: false)] MyEntity $entity,
            Request $request,
            RestConfig $restConfig,
            EntityManagerInterface $em
        ): array {
            $form = $restConfig->processedForm($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // do some stuff here
                $em->persist($entity);
                $em->flush();

                return $restConfig->isRest($request) ? ['entity' => $entity] : new RedirectResponse('/my-entity');
            } elseif ($form->isSubmitted() && !$form->isValid() && $restConfig->isRest($request)) {
                throw new FormValidationException($form);
            }

            return [
                'form' => $form,
                'entity' => $entity,
            ]
        }
   }
   ```

#### Configuration options

##### Full configuration options:

```yaml
# config/packages/wizzaq_rest.yaml
wizzaq_rest:
    use_protocols: true # set to false to completely disable `ProtocolListener`

    default_protocol: null # uses first defined protocol by default

    protocols: # available protocols
        rest: true # only one simple protocol defined by bundle right now ^_^

    default_response_section: null # if not null, then only defined section of returned result will be used as response for rest route

    serializer: null # serializer service id (autoselect by default from jms_serializer/serializer)
```

##### Rest argument options

> ``routes``: `null`|`string`|`array`
>
> Apply only to defined routes
>
> ---
>
> ``protocol``: `?string`
>
> Protocol name to use
>
> ---
>
> ``responseSection``: `?string`
>
> Only defined section of returned result will be used as response
>
> ---
>
> ``responseSerializationGroups``: `null`|`string`|`array`
>
> Pass defined serialization groups to serializer when serialize response by protocol

#### Create own protocol

If you want to create you own protocol just implement `Wizzaq\RestBundle\Protocol\NamedProtocolInterface` and it will be tagged as protocol by autoconfiguration

```php
namespace App\Protocol;

use Wizzaq\RestBundle\Config\RestConfig;
use Wizzaq\RestBundle\Protocol\NamedProtocolInterface;
use Wizzaq\RestBundle\Protocol\RestProtocol;

class MyProtocol extends RestProtocol implements NamedProtocolInterface
{
    public function __construct(RestConfig $restConfig, bool $debug = false, $serializer = null) 
    {
        parent::__construct($restConfig, $debug, $serializer);
    }
    
    public function getProtocolName(): string
    {
        return 'my_rest';
    }
    
    public function processResponse($response, Request $request): Response
    {
        return parent::processResponse(['success' => true, 'data' => $response], $request);
    }
}
```

Or implement `Wizzaq\RestBundle\Protocol\ProtocolInterface` and tag service manually with `alias` attribute:

```yaml
# config/services.yaml
...
services:
    ...
    App\Protocol\MyProtocol:
        tags:
            - { name: 'wizzaq_rest.protocol', alias: 'my_rest' }
```

Then you can disable unnecessary default protocol:

```yaml
# config/packages/wizzaq_rest.yaml
wizzaq_rest:
    default_protocol: 'my_rest' # not nessesary if you have only one protocol
    protocols:
        rest: false
```

## Bonus

### CircularReferenceHandler

Lost [CircularReferenceHandler](https://github.com/wizzaq/rest-bundle/blob/main/Serializer/CircularReferenceHandler.php) for [Symfony Serializer](https://symfony.com/doc/current/reference/configuration/framework.html#circular-reference-handler).

```yaml
# config/packages/framework.yaml
framework:
    ...
    serializer:
        circular_reference_handler: 'wizzaq_rest.serializer.circular_reference_handler'
```
