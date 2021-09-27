<?php
namespace App\Service;

// use Symfony\Bridge\Doctrine\ManagerRegistry;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\ExpressionValidator;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;

class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    protected $validators = [];
    protected $mr;

    public function __construct(ManagerRegistry $mr)
    {
        $this->mr = $mr;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(Constraint $constraint)
    {
        $className = $constraint->validatedBy();

        if (!isset($this->validators[$className])) {
            switch ($className) {
                case 'validator.expression':
                    $this->validators[$className] = new ExpressionValidator();
                    break;
                case 'doctrine.orm.validator.unique':
                    $this->validators[$className] = new UniqueEntityValidator($this->mr);
                    break;
                default:
                    $this->validators[$className] = new $className();
            }
        }

        return $this->validators[$className];
    }
}
