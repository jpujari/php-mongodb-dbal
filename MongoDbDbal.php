<?php
namespace MongoDbDbal;

use MongoDB\Collection as Collection;
use MongoDb\Driver\Manager as Manager;
use MongoDB\BSON\ObjectID as ObjectID;

class MongoDbDbal
{
    protected $collectionName;
    protected $collection;
    protected $dbName;

    public function  __construct($dbHost, $dbPort, $dbName)
    {
        $this->dbHost = $dbHost;
        $this->dbPort = $dbPort;
        $this->dbName = $dbName;
        $this->manager = new Manager("mongodb://$this->dbHost:$this->dbPort");
    }

    /**
     * @param $document
     * @return string
     */
    public function insertOne($document)
    {
        $result = $this->getCollection()->insertOne($document);
        return (string)$result->getInsertedId();
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function find($filter = [])
    {
        $results = $this->getCollection()->find($filter);
        $convertedResults = $this->convertMongoIds($results->toArray());
        return $convertedResults;
    }

    /**
     * @param $documentId
     * @param array $filter
     * @return mixed
     */
    public function findOne($documentId, $filter = [])
    {
        $idFilter = ['_id' => new ObjectID($documentId)];
        $filter = array_merge($filter, $idFilter);
        $cursor = (array)$this->getCollection()->findOne($filter);
        $convertedResult = $this->convertMongoId($cursor);
        return $convertedResult;
    }

    /**
     * @param $documentId
     * @param $document
     * @param array $filter
     * @return mixed
     */
    public function updateOne($documentId, $document, $filter = [])
    {
        $idFilter = ['_id' => new ObjectID($documentId)];
        $filter = array_merge($filter, $idFilter);
        //update needs the $set operator
        $update = ['$set' => $document];
        $updateResult = $this->getCollection()->updateOne($filter, $update);
        return $updateResult->isAcknowledged();
    }

    /**
     * @param $documentId
     * @param array $filter
     * @return mixed
     */
    public function deleteOne($documentId, $filter = [])
    {
        $idFilter = ['_id' => new ObjectID($documentId)];
        $filter = array_merge($filter, $idFilter);
        $deleteResult = $this->getCollection()->deleteOne($filter);
        return $deleteResult->isAcknowledged();
    }

    /**
     * @param $name
     */
    public function setCollectionName($name)
    {
        $this->collectionName = $name;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }

    /**
     * @return Collection
     */
    protected function getCollection()
    {
        if (empty($this->collection)) {
            $this->collection = new Collection($this->manager, $this->dbName, $this->getCollectionName());
        }
        return $this->collection;
    }


    /**
     * Utility function to convert mongo ids
     * @param $results
     * @return mixed
     */
    protected function convertMongoIds(array $results)
    {
        //checking for array of arrays
        if (isset($results[0]) && is_array($results[0])) {
            foreach ($results as $result) {
                $result['_id'] = (string)$result['_id'];
            }
        } else {
            $results['_id'] = (string)$results['_id'];
        }
        return $results;
    }
}

