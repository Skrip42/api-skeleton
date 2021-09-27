<?php
namespace App\EntityListener;

use App\Entity\ValidationInterface as Entity;
use App\Service\ConstraintValidatorFactory;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validation;

class ValidateListener
{
    private $validator;

    public function __construct(ManagerRegistry $mr)
    {
        $factory = new ConstraintValidatorFactory($mr);
        $this->validator = Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory($factory)
            ->enableAnnotationMapping()
            ->getValidator();
    }

    public function prePersist(Entity $entity, LifecycleEventArgs $event)
    {
        /** @var ConstraintViolationList $validations */
        $validations = $this->validator->validate($entity);
        if ($validations->count() == 0) {
            return;
        }
        $errors = [];
        /** @var ConstraintViolation $validate */
        foreach ($validations as $validate) {
            $errors[] = $validate->getMessage();
        }
        throw new ValidatorException(implode(',', $errors));
    }
}
