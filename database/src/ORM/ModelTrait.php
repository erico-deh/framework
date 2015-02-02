<?php

namespace Pagekit\Database\ORM;

use Pagekit\Database\Connection;

trait ModelTrait
{
    /**
     * Gets the related EntityManager.
     *
     * @return EntityManager
     */
    public static function getManager()
    {
        static $manager;

        if (!$manager) {
            $manager = EntityManager::getInstance();
        }

        return $manager;
    }

    /**
     * @return Connection
     */
    public static function getConnection()
    {
        return static::getManager()->getConnection();
    }

    /**
     * Gets the related Metadata object with mapping information of the class.
     *
     * @return Metadata
     */
    public static function getMetadata()
    {
        return static::getManager()->getMetadata(get_called_class());
    }

    /**
     * Create a new QueryBuilder instance.
     *
     * @return QueryBuilder
     */
    public static function query()
    {
        return new QueryBuilder(static::getManager(), static::getMetadata());
    }

    /**
     * Create a new QueryBuilder instance and set the WHERE condition.
     *
     * @param  mixed $condition
     * @param  array $params
     * @return QueryBuilder
     */
    public static function where($condition, array $params = [])
    {
        return static::query()->where($condition, $params);
    }

    /**
     * Retrieve an entity by its identifier.
     *
     * @param  mixed $id
     * @return mixed
     * @throws \Exception
     */
    public static function find($id)
    {
        if ($entity = static::getManager()->getById($id, get_called_class())) {
            return $entity;
        }

        return static::where([static::getMetadata()->getIdentifier() => $id])->first();
    }

    /**
     * Retrieve all entities.
     *
     * @return mixed
     */
    public static function findAll()
    {
        return static::query()->get();
    }

    /**
     * Saves the entity.
     *
     * @param array $data
     */
    public function save(array $data = [])
    {
        static::getManager()->save($this, $data);
    }

    /**
     * Deletes the entity.
     */
    public function delete()
    {
        static::getManager()->delete($this);
    }
}
