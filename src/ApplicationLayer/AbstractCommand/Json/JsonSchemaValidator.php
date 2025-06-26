<?php

namespace App\ApplicationLayer\AbstractCommand\Json;

use App\ApplicationLayer\AbstractCommand\CommandValidationResult;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;

class JsonSchemaValidator
{
    /**
     * @param object $subject
     * @param string $pathToSchemaFile
     *
     * @return CommandValidationResult
     */
    public function validate($args, string $schemaFileUri)
    {
        $validationResult = new CommandValidationResult($args);

        try {
            $schema = Schema::import($schemaFileUri);
            $schema->in($args);
        } catch (InvalidValue $exception) {
            $error = $exception->inspect();
            $validationResult->addError($error->dataPointer, $error->error);
        } catch (\Exception $exception) {
            $validationResult->addError($schemaFileUri, 'Not found');
        }

        return $validationResult;
    }
}
