<?php

namespace Tests\Fixtures;

class PrototypeDataset
{
    /**
     * Get the complete raw fixture array.
     *
     * @return array<string, mixed>
     */
    public static function raw(): array
    {
        $path = resource_path('fixtures/prototype.json');

        if (! file_exists($path)) {
            throw new \RuntimeException("Fixture file not found at: {$path}");
        }

        $json = file_get_contents($path);

        return json_decode($json, true) ?: [];
    }

    /**
     * Get all fictitious projects.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function projects(): array
    {
        return static::raw()['projects'] ?? [];
    }

    /**
     * Get all fictitious progresses.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function progresses(): array
    {
        return static::raw()['progresses'] ?? [];
    }

    /**
     * Get all draft entries.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function drafts(): array
    {
        return static::raw()['drafts'] ?? [];
    }

    /**
     * Find project by ID or JO No.
     *
     * @return array<string, mixed>|null
     */
    public static function findProject(string $idOrJo): ?array
    {
        foreach (static::projects() as $project) {
            if ($project['id'] === $idOrJo || $project['jo_no'] === $idOrJo) {
                return $project;
            }
        }

        return null;
    }

    /**
     * Find progress by ID.
     *
     * @return array<string, mixed>|null
     */
    public static function findProgress(string $id): ?array
    {
        foreach (static::progresses() as $progress) {
            if ($progress['id'] === $id) {
                return $progress;
            }
        }

        return null;
    }

    /**
     * Get the designated complex progress for testing relations.
     *
     * @return array<string, mixed>|null
     */
    public static function complexProgress(): ?array
    {
        return static::findProgress('prog-001');
    }
}
