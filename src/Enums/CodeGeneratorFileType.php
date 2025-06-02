<?php

namespace Mruganshi\CodeGenerator\Enums;

/**
 * Enum CodeGeneratorFileType
 *
 * This enum represents the different types of files that can be generated
 * within the Code Generator package. Each case corresponds to
 * a specific type of file that can be created during the code generation process.
 */
enum CodeGeneratorFileType: string
{
    case CONTROLLER = "Controller";

    case SERVICE = "Service";

    case MODEL = "Model";

    case FACTORY = "Factory";

    case MIGRATION = "Migration";

    case OBSERVER = "Observer";

    case POLICY = "Policy";

    case RESOURCE = "Resource";

    case RESOURCE_COLLECTION = "Resource-Collection";

    case TRAIT = "Trait";

    case REQUEST = "Request";

    case NOTIFICATION = "Notification";
}
