<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model $model 
     */
    protected $model;

    /**
     * Construct the corresponding model
     */
    public function __construct()
    {
        $this->setModel();
    }

    abstract public function getModel();

    /**
     * Get all record from db
     */
    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    /**
     * Get all
     * 
     * @return mixed
     */
    public function getAll()
    {
        return $this->model->orderBy('id', 'desc')->get();
    }

    /**
     * Find by id
     * 
     * @param $id
     * 
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * create a new record
     * 
     * @param array $data
     * 
     * @return mixed
     */
    public function create($data = [])
    {
        return $this->model->create($data);
    }

    /**
     * update a record based on record id
     * 
     * @param $id
     * @param array $data
     * 
     * @return mixed
     */
    public function update($id, $data = [])
    {
        $record = $this->find($id);

        if ($record) {
            $record->update($data);
            return $record;
        }

        return false;
    }

    /**
     * delete a record based on record id
     * 
     * @param $id
     * 
     * @return mixed
     */
    public function delete($id)
    {
        $record = $this->find($id);

        if ($record) {
            $record->delete();
            return true;
        }

        return false;
    }
}
