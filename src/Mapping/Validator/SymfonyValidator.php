<?php declare(strict_types = 1);

namespace Sabservis\Api\Mapping\Validator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Sabservis\Api\Exception\Api\ValidationException;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use function assert;
use function count;
use function method_exists;

class SymfonyValidator implements EntityValidator
{

	private ConstraintValidatorFactoryInterface|null $constraintValidatorFactory = null;

	public function __construct(private Reader|null $reader = null)
	{
		AnnotationReader::addGlobalIgnoredName('mapping');
	}

	public function setConstraintValidatorFactory(ConstraintValidatorFactoryInterface $constraintValidatorFactory): void
	{
		$this->constraintValidatorFactory = $constraintValidatorFactory;
	}

	/**
	 * @throws ValidationException
	 *
	 * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
	 */
	public function validate(object $entity): void
	{
		$validatorBuilder = Validation::createValidatorBuilder();
		$validatorBuilder->enableAttributeMapping();

		if (method_exists($validatorBuilder, 'setDoctrineAnnotationReader')) {
			$validatorBuilder->setDoctrineAnnotationReader($this->reader);
		}

		if ($this->constraintValidatorFactory !== null) {
			$validatorBuilder->setConstraintValidatorFactory($this->constraintValidatorFactory);
		}

		$validator = $validatorBuilder->getValidator();

		$violations = $validator->validate($entity);
		assert($violations instanceof ConstraintViolationListInterface);

		if (count($violations) > 0) {
			$fields = [];

			foreach ($violations as $violation) {
				$fields[$violation->getPropertyPath()][] = $violation->getMessage();
			}

			throw ValidationException::create()
				->withFields($fields);
		}
	}

}
