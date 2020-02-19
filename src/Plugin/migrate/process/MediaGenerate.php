<?php

namespace Drupal\migrate_custom\Plugin\migrate\process;

use Drupal\media\Entity\Media;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityGenerate;

/**
 * Generate a media entity with specified metadata.
 *
 * This plugin is to be used by migrations which have media entity reference
 * fields.
 *
 * Available configuration keys:
 * - destinationField: the name of the file field on the media entity.
 *
 * @code
 * process:
 *   'field_files/target_id':
 *     -
 *       plugin: migration_lookup
 *       migration: my_file_migration
 *       source: field_image/0/fid
 *     -
 *       plugin: media_generate
 *       destinationField: image
 *       imageAltSource: field_image/0/alt
 *       imageTitleSource: field_image/0/title
 *
 * @endcode
 *
 * If image_alt_source and/or image_title_source configuration parameters
 * are provided, alt and/or title image properties will be fetched from provided
 * source fields (if available) and pushed into media entity
 *
 * @MigrateProcessPlugin(
 *   id = "media_generate"
 * )
 */
class MediaGenerate extends EntityGenerate {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrateExecutable, Row $row, $destinationProperty) {
    if (!isset($this->configuration['destinationField'])) {
      throw new MigrateException('Destination field must be set.');
    }

    // First load the target_id of the file referenced via the migration.
    /* @var /Drupal/file/entity/File $file */
    $file = $this->entityManager->getStorage('file')->load($value);

    if (empty($file)) {
      throw new MigrateException('Referenced file does not exist');
    }

    // Creates a media entity if the lookup determines it doesn't exist.
    $fileName = $file->label();
    if (!($entityId = parent::transform($fileName, $migrateExecutable, $row, $destinationProperty))) {
      return NULL;
    }

    $entity = Media::load($entityId);

    $fileId = $file->id();

    $destinationFieldValues = $entity->{$this->configuration['destinationField']}->getValue();
    $destinationFieldValues[0]['target_id'] = $fileId;

    $this->insertPropertyIntoDestinationField($destinationFieldValues, $row, 'alt', 'imageAltSource');
    $this->insertPropertyIntoDestinationField($destinationFieldValues, $row, 'title', 'imageTitleSource');

    $entity->{$this->configuration['destinationField']}->setValue($destinationFieldValues);
    $entity->save();

    return $entityId;
  }

  protected function insertPropertyIntoDestinationField(array &$destinationFieldValues, Row $row, $propertyKey, $configurationKey) {
    // Set alt and title into media entity if not empty
    if (isset($this->configuration[$configurationKey])) {
      $propertyValue = $row->getSourceProperty($this->configuration[$configurationKey]);
      if (!empty($propertyValue)) {
        $destinationFieldValues[0][$propertyKey] = $propertyValue;
      }
    }
  }
}