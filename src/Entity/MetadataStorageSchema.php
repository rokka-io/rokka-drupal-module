<?php

namespace Drupal\rokka\Entity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 *
 */
class MetadataStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    switch ($field_name) {
      case 'uri':
        $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
        break;
    }

    return $schema;
  }

}
