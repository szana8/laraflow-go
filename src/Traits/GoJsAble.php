<?php


namespace szana8\LaraflowGo\Traits;


trait GoJsAble
{
    /**
     * States attribute for GoJS
     *
     * @var
     */
    protected $laraflowConfiguration;

    /**
     * Initialize node array for GoJS states
     *
     * @var array
     */
    protected $nodeDataArray = [];

    /**
     * Initialize links array for GoJS transitions
     *
     * @var array
     */
    protected $linkDataArray = [];

    /**
     * Return the array of the states which belongs
     * to the proper workflow
     *
     * @return array
     */
    public function getNodeArrayAttribute()
    {
        return $this->nodeDataArray;
    }

    /**
     * Load the workflow attribute.
     *
     * @return string
     */
    public function getGoJsObjectAttribute()
    {
        return $this->compile($this->configuration);
    }

    /**
     * Convert the original state array to the appropriate format
     *
     * @param $configuration
     * @return string
     */
    public function compile($configuration)
    {
        $this->laraflowConfiguration = json_decode($configuration, true);

        return $this->collectGoJs();
    }

    /**
     * Collect all of the necessary attribute for GoJS than
     * cast to json string
     *
     * @return string
     */
    protected function collectGoJs()
    {
        return collect([
            'class' => 'go.GraphLinksModel',
            'linkFromPortIdProperty' => 'fromPort',
            'linkToPortIdProperty' => 'toPort',
            'nodeDataArray' => $this->getNodeDataArray(),
            'linkDataArray' => $this->getLinkDataArray()
        ])->toJson();
    }

    /**
     * Collect the steps to an array
     *
     * @return mixed
     */
    protected function getNodeDataArray()
    {
        if(count($this->laraflowConfiguration['steps']) == 0)
            return $this->nodeDataArray;

        collect($this->laraflowConfiguration['steps'])->map(function($value, $key) {
            array_push($this->nodeDataArray, [
                'category' => $value['extra']['category'],
                'text' => $value['text'],
                'key' => $key,
                'loc' => $value['extra']['loc'],
            ]);
        });

        return $this->nodeDataArray;
    }

    /**
     * Collect the transaction links to an array
     *
     * @return array
     */
    protected function getLinkDataArray()
    {
        if(count($this->laraflowConfiguration['transitions']) == 0)
            return $this->linkDataArray;

        collect($this->laraflowConfiguration['transitions'])->map(function($value) {
            array_push($this->linkDataArray, [
                'from' => $value['from'],
                'to' => $value['to'],
                'fromPort' => $value['extra']['fromPort'],
                'toPort' => $value['extra']['toPort'],
                'points' => $value['extra']['points'],
                'text' => $value['text'],
                'visible' => true
            ]);
        });

        return $this->linkDataArray;
    }

    /**
     * Convert the front end state format back to the original array what we
     * store in the database
     *
     * @param $configuration
     * @return mixed
     */
    public function decompile($configuration)
    {
        return collect([
            'property_path' => json_decode($this->configuration, true)['property_path'],
            'steps' => $this->decompileSteps(json_decode($configuration, true)['nodeDataArray']),
            'transitions' => $this->decompileLinks($configuration)
        ])->toJson();
    }

    /**
     * Convert the GoJS workflow states to the proper format
     * for the state machine
     *
     * @param $steps
     * @return array
     */
    protected function decompileSteps($steps)
    {
        collect($steps)->map(function($value) {
            array_push($this->nodeDataArray, [
                'text' =>  $value['text'],
                'extra' => [
                    'color' => $value['category'],
                    'category' => $value['category'],
                    'loc' => $value['loc'],
                ]
            ]);
        });

        return  $this->nodeDataArray;
    }

    /**
     * Convert the GoJS workflow links to the proper format
     * for the state machine
     *
     * @param $workflow
     * @return void
     */
    protected function decompileLinks($workflow)
    {
        collect(json_decode($workflow, true)['linkDataArray'])->map(function ($value) use ($workflow) {
            array_push($this->linkDataArray, [
                'from' => $this->getStateByKey(json_decode($workflow, true)['nodeDataArray'], $value['from']),
                'to' =>  $this->getStateByKey(json_decode($workflow, true)['nodeDataArray'], $value['to']),
                'text' => $value['text'],
                'extra' => [
                    'fromPort' => $value['fromPort'],
                    'toPort' => $value['toPort'],
                    'points' => json_encode($value['points'])
                ],
                'callbacks' => [
                    'pre' => [],
                    'post' => []
                ],
                'validators' => []
            ]);
        });

        return $this->linkDataArray;
    }

    /**
     * Return the value of the key which belongs to the given
     * state.
     *
     * @param $workflow
     * @param $searchKey
     * @return int|string
     */
    protected function getStateByKey($workflow, $searchKey)
    {
        foreach($workflow as $key => $value)
        {
            if($value['key'] == $searchKey) {
                return $key;
            }
        }
    }
}