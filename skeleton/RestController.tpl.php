<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
use <?= $repository_full_class_name ?>;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("<?= $route_path ?>", name="<?= $route_name ?>")
 */
class <?= $class_name ?> extends AbstractApiController<?= "\n" ?>
{
    /**
     * @Route(
     *      "/",
     *      name="_search",
     *      methods={"GET"}
     *      )
     */
    public function search(Request $request): Response
    {
        /** @var <?= $repository_class_name ?> $repository */
        $repository = $this->getDoctrine()->getRepository(<?= $entity_class_name ?>::class);
        $params = $request->query->all();
        unset($params['page']);
        unset($params['perPage']);
        unset($params['sort']);
        $page = $request->query->getInt('page', 1);
        $count = $repository->count([]);
        $perPage = $request->query->getInt('perPage', $count);
        $sort = $request->query->get('sort');
        $<?= $entity_var_plural ?> = $repository->findBy(
            $params,
            $sort,
            $perPage,
            $perPage * ($page - 1)
        );
        return $this->api(
            $<?= $entity_var_plural ?>,
            [
                'currentPage' => $page,
                'perPage'     => $perPage,
                'pagesTotal'  => $count
            ]
        );
    }

    /**
     * @Route(
     *      "/",
     *      name="_create",
     *      methods={"POST"}
     *      )
     */
    public function create(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
<?
        $t = "    ";
        $br = "\n";
        foreach ($entity_setters as $setter) {
            $param = $setter->getParameters()[0];
            $is_simple = strpos($param->getType()->getName(), '\\') === false;
            if ($is_simple) {
                echo $t.$t . '$' . $entity_var_singular . '->' . $setter->getName()
                    . '($request->request->get("' . $param->getName()
                    . '"));' . $br;
            } else {
                echo $t.$t . '$' . $entity_var_singular . '->' . $setter->getName()
                    . '(' . $br.$t.$t.$t . '$this->getDoctrine()' . $br.$t.$t.$t.$t
                    .'->getRepository("'
                    . $param->getType()->getName() . '")' . $br.$t.$t.$t.$t
                    . '->find('
                    . '$request->request->get("' . $param->getName() . '_id")'
                    . ')' . $br.$t.$t . ');' . $br;
            }
        }
?>
        $em->persist($<?= $entity_var_singular ?>);
        $em->flush();
        $response = $this->api($<?= $entity_var_singular ?>, ['group' => 'create'])
            ->setStatusCode(Response::HTTP_CREATED);
        $response->headers->set(
            'Location',
            $this->generateUrl(
                "<?= $route_name ?>_get",
                ["<?= $entity_identifier?>" => $<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>()]
            )
        );
        return $response;
    }

    /**
     * @Route(
     *      "/{<?= $entity_identifier?>}",
     *      name="_get",
     *      methods={"GET"},
     *      requirements={"id":"<?= $entity_identifier_pattern?>"}
     *      )
     */
    public function read(Request $request, $<?= $entity_identifier?>): Response
    {
        $<?= $entity_var_singular ?> = $this->getDoctrine()->getRepository(<?= $entity_class_name ?>::class)->find($<?= $entity_identifier?>);
        if (empty($<?= $entity_var_singular ?>)) {
            return $this->api()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        return $this->api($<?= $entity_var_singular ?>);
    }

    /**
     * @Route(
     *      "/{<?= $entity_identifier?>}",
     *      name="_update",
     *      methods={"PUT"},
     *      requirements={"id":"<?= $entity_identifier_pattern?>"}
     *      )
     */
    public function update(Request $request, $<?= $entity_identifier?>): Response
    {
        $em = $this->getDoctrine()->getManager();
        $<?= $entity_var_singular ?> = $this->getDoctrine()->getRepository(<?= $entity_class_name ?>::class)->find($<?= $entity_identifier?>);
        $isCreated = false;
        if (empty($<?= $entity_var_singular ?>)) {
            $<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
            $isCreated = true;
        }
<?
        $t = "    ";
        $br = "\n";
        foreach ($entity_setters as $setter) {
            $param = $setter->getParameters()[0];
            $is_simple = strpos($param->getType()->getName(), '\\') === false;
            if ($is_simple) {
                echo $t.$t . '$' . $entity_var_singular . '->' . $setter->getName()
                    . '($request->request->get("' . $param->getName()
                    . '"));' . $br;
            } else {
                echo $t.$t . '$' . $entity_var_singular . '->' . $setter->getName()
                    . '(' . $br.$t.$t.$t . '$this->getDoctrine()' . $br.$t.$t.$t.$t
                    .'->getRepository("'
                    . $param->getType()->getName() . '")' . $br.$t.$t.$t.$t
                    . '->find('
                    . '$request->request->get("' . $param->getName() . '_id")'
                    . ')' . $br.$t.$t . ');' . $br;
            }
        }
?>
        if ($isCreated) {
            $em->persist($<?= $entity_var_singular ?>);
            $em->flush();
            $response = $this->api($<?= $entity_var_singular ?>, ['group' => 'create'])
                ->setStatusCode(Response::HTTP_CREATED);
            $response->headers->set(
                'Location',
                $this->generateUrl(
                    "<?= $route_name ?>_get",
                    ["<?= $entity_identifier?>" => $<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>]
                )
            );
            return $response;
        } else {
            $em->flush();
            return $this->api();
        }
    }

    /**
     * @Route(
     *      "/{<?= $entity_identifier?>}",
     *      name="_patch",
     *      methods={"PATCH"},
     *      requirements={"id":"<?= $entity_identifier_pattern?>"}
     *      )
     */
    public function patch(Request $request, $<?= $entity_identifier?>): Response
    {
        $em = $this->getDoctrine()->getManager();
        $<?= $entity_var_singular ?> = $this->getDoctrine()->getRepository(<?= $entity_class_name ?>::class)->find($<?= $entity_identifier?>);
        if (empty($<?= $entity_var_singular ?>)) {
            return $this->api()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
<?
        $t = "    ";
        $br = "\n";
        foreach ($entity_setters as $setter) {
            $param = $setter->getParameters()[0];
            $is_simple = strpos($param->getType()->getName(), '\\') === false;
            echo $t.$t . 'if ($request->request->has("'
                . $param->getName() . '")) {' . $br;
            if ($is_simple) {
                echo $t.$t.$t . '$' . $entity_var_singular . '->' . $setter->getName()
                    . '($request->request->get("' . $param->getName()
                    . '"));' . $br;
            } else {
                echo $t.$t.$t . '$' . $entity_var_singular . '->' . $setter->getName()
                    . '(' . $br.$t.$t.$t.$t . '$this->getDoctrine()' . $br.$t.$t.$t.$t.$t
                    .'->getRepository("'
                    . $param->getType()->getName() . '")' . $br.$t.$t.$t.$t.$t
                    . '->find('
                    . '$request->request->get("' . $param->getName() . '_id")'
                    . ')' . $br.$t.$t.$t . ');' . $br;
            }
            echo $t.$t . "}" . $br;
        }
?>
        $em->flush();
        return $this->api();
    }

    /**
     * @Route(
     *      "/{<?= $entity_identifier?>}",
     *      name="_delete",
     *      methods={"DELETE"},
     *      requirements={"id":"<?= $entity_identifier_pattern?>"}
     *      )
     */
    public function delete(Request $request, $<?= $entity_identifier?>): Response
    {
        $em = $this->getDoctrine()->getManager();
        $<?= $entity_var_singular ?> = $this->getDoctrine()->getRepository(<?= $entity_class_name ?>::class)->find($<?= $entity_identifier?>);
        if (empty($<?= $entity_var_singular ?>)) {
            return $this->api()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $em->remove($<?= $entity_var_singular ?>);
        $em->flush();
        return $this->api();
    }
}
