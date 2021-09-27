# api-skeleton

symfony api skeleton

## Features out of the box

- authorization by token
- serializer for api with error catching
- put/patch parameters available
- correct validation of the entity without form
- logging requests
- crun-rest maker

## Creating new project

```bash
composer create-project skrip42/api-skeleton <project_name>
```

create .env.local and and add environment variable definitions to it:

- API_KEY - you api authentification key

## Usage

### Serializer

```php
/** in you controller */

class CustomController extends AbstractApiController //extends AbstractApiController
{
    public function sameMethod() : Response
    {
        ...
        //use api(mixed $data, array $meta = []) method to serialize response
        return $this->api($dataOrEntity);
    }

    ...
```

#### use serialization group

```php
    //in you entity/DOT class
    /** @Groups("groupName") */
    private $field; //add annotation to filed

    /** @Groups("groupName") */
    public function GetField(): fieldtype //or to you public method

    //in you controller
    return $this->api(
        $dataOrEntity,
        [
            'groups' => 'groupName'
        ]
    );
```

#### use pagination

```php
    //in you controller print some like this
    $page = $request->query->getInt('page', 1);
    $count = $repository->count([]);
    $perPage = $request->query->getInt('perPage', $count);
    $sort = $request->query->get('sort');
    $entities = $repository->findBy(
        $params,
        $sort,
        $perPage,
        $perPage * ($page - 1)
    );
    $this->api(
        $entities,
        [
            'currentPage' => $page,
            'perPage' => $perPage,
            'pagesTotal' => ceil($count / $perPage)
        ]
    );
```

#### error with http status code

```php
use App\Exceptions\ApiException;
...
throw new ApiException($message, $statusCode);
```

### Entity validation (doctrineORM required)

- uncomment App\EntityListener block in services.yaml
- implement App\Entity\ValidationInterface in you Entity
- add @ORM\EntityListeners({"App\EntityListener\ValidateListener"}) annotation in you Entity
- profit!

### RestCRUD maker (doctrineORM required)

- uncomment App\Maker\MakeRest block in services.yaml
- run ./bin/console make:rest command to create RestCRUD controller for you entity
