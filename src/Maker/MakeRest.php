<?php
namespace App\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Doctrine\Common\Inflector\Inflector as LegacyInflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Question\Question;
//dependencies
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;

final class MakeRest extends AbstractMaker
{

    private $doctrineHelper;
    private $controllerClassName;
    private $inflector;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
        if (class_exists(InflectorFactory::class)) {
            $this->inflector = InflectorFactory::create()->build();
        }
    }

    public static function getCommandName(): string
    {
        return 'make:rest';
    }

    public static function getCommandDescription()
    {
        return 'Creates rest CRUD for Doctrine entity class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            // ->setName('make:rest')
            ->addArgument(
                'entity-class',
                InputArgument::OPTIONAL,
                sprintf(
                    'The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)',
                    Str::asClassName(Str::getRandomTerm())
                )
            );
        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );
        // dump($entityClassDetails);
        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
        $entityMetadata = $this->doctrineHelper->getMetadata($entityClassDetails->getFullName());
        $entity_methods = $entityMetadata->getReflectionClass()->getMethods();
        $entity_setters = array_filter($entity_methods, function ($method) {
            return strpos($method->getName(), 'set') === 0;
        });
        $entityIdentifierPattern = ".+";
        if ($entityMetadata->getTypeOfField($entityDoctrineDetails->getIdentifier()) == 'integer') {
            $entityIdentifierPattern = "\d+";
        }
        // dump($entity_setters);
        // foreach ($entity_setters as $setter) {
        //     dump($setter->getName());
        //     dump($setter->getParameters());
        //     $param = $setter->getParameters()[0];
        //     dump($param->getName());
        //     dump($param->getType()->getName());
        //     dump(strpos($param->getType()->getName(), '\\') === false);
        // }
        // die();



        // $repositoryVars = [];

        // if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            // $repositoryClassDetails = $generator->createClassNameDetails(
            //     '\\'.$entityDoctrineDetails->getRepositoryClass(),
            //     'Repository\\',
            //     'Repository'
            // );
        //
        //     $repositoryVars = [
        //         'repository_full_class_name' => $repositoryClassDetails->getFullName(),
        //         'repository_class_name' => $repositoryClassDetails->getShortName(),
        //         'repository_var' => lcfirst($this->singularize($repositoryClassDetails->getShortName())),
        //     ];
        // }

        $controllerClassDetails = $generator->createClassNameDetails(
            $this->controllerClassName,
            'Controller\\',
            'Controller'
        );
        $repositoryClassDetails = $generator->createClassNameDetails(
            '\\'.$entityDoctrineDetails->getRepositoryClass(),
            'Repository\\',
            'Repository'
        );
        $entityVarPlural = lcfirst($this->pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst($this->singularize($entityClassDetails->getShortName()));
        $routeName = Str::asRouteName($controllerClassDetails->getRelativeNameWithoutSuffix());

        $generator->generateController(
            $controllerClassDetails->getFullName(),
            'skeleton/RestController.tpl.php',
            [
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'route_path' => Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix()),
                'route_name' => $routeName,
                'entity_var_plural' => $entityVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_identifier_pattern' => $entityIdentifierPattern,
                'entity_setters' => $entity_setters,
                'repository_full_class_name' => $repositoryClassDetails->getFullName(),
                'repository_class_name' => $repositoryClassDetails->getShortName(),

            ]
        );
        $generator->writeChanges();
        $this->writeSuccessMessage($io);
        $io->text(sprintf('Next: Check your new REST by going to <fg=yellow>%s/</>', Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix())));
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');

            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();

            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);

            $value = $io->askQuestion($question);

            $input->setArgument('entity-class', $value);
        }

        $defaultControllerClass = Str::asClassName(sprintf('%s Controller', $input->getArgument('entity-class')));

        $this->controllerClassName = $io->ask(
            sprintf('Choose a name for your controller class (e.g. <fg=yellow>%s</>)', $defaultControllerClass),
            $defaultControllerClass
        );
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'router'
        );

        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );

        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );

        $dependencies->addClassDependency(
            ParamConverter::class,
            'annotations'
        );
    }

    private function pluralize(string $word): string
    {
        if (null !== $this->inflector) {
            return $this->inflector->pluralize($word);
        }

        return LegacyInflector::pluralize($word);
    }

    private function singularize(string $word): string
    {
        if (null !== $this->inflector) {
            return $this->inflector->singularize($word);
        }

        return LegacyInflector::singularize($word);
    }
}
