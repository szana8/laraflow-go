<template>
    <div class="container-fluid">

        <create-status-dialog @created="addStatus"></create-status-dialog>

        <callback-configuration-dialog @configured="createWorkflowSnapshot"></callback-configuration-dialog>

        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <button class="btn btn-outline-secondary btn-sm" @click="show">Add Status</button>
                        <div class="form-check form-check-inline ml-3 mt-1">
                            <input class="form-check-input" type="checkbox" id="showLabel" value="true" v-model="showTransition" @click="setLinksTextVisible">
                            <label class="form-check-label" for="showLabel">Show transition labels</label>
                        </div>
                        <div class="pull-right">
                            <button class="btn btn-primary btn-sm" @click="save">Save</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-10" style="height: 600px;" id="workflow-designer-editor"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <textarea name="workflow" id="workflow" rows="10" v-model="gojsDiagram" class="form-control d-none"></textarea>
            </div>
        </div>

    </div>
</template>

<script>
    let laraflowGo = require('../../LaraflowGo');
    import { EventBus } from '../../event-bus.js';
    import createStatusDialog from './CreateStatusDialog'
    import callbackConfigurationDialog from './CallbackConfigurationDialog'

    export default {
        props: [
            'configuration',
            'endpoint'
        ],

        components: {
            createStatusDialog,
            callbackConfigurationDialog
        },

        data() {
            return {
                id: this.configuration.id,
                name: null,
                category: null,
                defaultCategories: [],
                showTransition: true,
                links: this.configuration.linkDataArray,
                gojsDiagram: this.configuration.goJsObject,
                callbackObjects: {
                    validators: this.configuration.validators
                }
            }
        },

        mounted() {
            // Set the callback object to glabaly.
            window.callbackObjects = this.callbackObjects;
            // Have to initialize the workflow first
            laraflowGo.initLaraflowGo('workflow-designer-editor');
            // Load workflow diagram from the property
            this.setGoJsJsonDiagram(this.gojsDiagram)
            // Have to listen this event, because we can not
            // access to the attributes from the workflow
            // function directly
            EventBus.$on('setLinkLabel', this.setLinkLabel);
            // Check the duplicate steps in the workflow, the
            // workflow can not have duplicate steps.
            EventBus.$on('checkDuplications', this.checkDuplications);

            // Listener for the link double click action to open
            // the action dialog
            window.laraflowGo.addDiagramListener("ObjectDoubleClicked", function (e) {
                if (e.subject.part.type.Sb == 'Link') {
                    EventBus.$emit('showConfigureCallbacksModal', e, window.callbackObjects);
                }
            });
            // Listener for the link drawn action to create
            // a label for the created link
            window.laraflowGo.addDiagramListener("LinkDrawn", function (e) {
                if (e.subject.part.type.Sb == 'Link') {
                    //open the transition dialog
                    EventBus.$emit('setLinkLabel', e);
                }
            });
        },

        methods: {
            // Check the node what the user wants to add to the diagram
            // is already exist or not. If yes we denied the process
            checkDuplications(node) {
                var isValid = true;
                var nodeDataArray = JSON.parse(window.laraflowGo.model.toJson());

                isValid = nodeDataArray.nodeDataArray.forEach(function (element) {
                    if (element.text == node) {
                        return false;
                    }
                });

                return isValid;
            },

            // Set the label of the created link between two nodes
            // based on the name of the nodes
            setLinkLabel(evt) {
                var fromNode, toNode;
                var nodeDataArray = JSON.parse(window.laraflowGo.model.toJson());

                nodeDataArray.nodeDataArray.forEach(function (element) {
                    toNode = (element.key == evt.subject.part.data.to) ? element.text : toNode;
                    fromNode = (element.key == evt.subject.part.data.from) ? element.text : fromNode;
                });

                window.laraflowGo.model.setDataProperty(evt.subject.part.data, 'text', fromNode + ' to ' + toNode);
                this.createWorkflowSnapshot();
            },

            // Set the GoJS diagram based on the given json string
            setGoJsJsonDiagram(diagram) {
                window.laraflowGo.model = go.Model.fromJson(diagram);
            },

            // Show the new workflow status modal to add a new
            // item to the workflow
            show() {
                EventBus.$emit('showCreateWorkflowStatusModal');
            },

            // Save the current status of the workflow
            save() {
                // First we have to create a snapshot from the actual
                // state of the workflow diagram and pass the json
                // output to the configuration textarea
                this.createWorkflowSnapshot();
                // Update the workflow configuration with the new
                // value. Use the endpoint property for the link.
                axios.put(this.endpoint, {
                    configuration: this.gojsDiagram
                }).then((response) => {
                    // If the response is completed normally fire
                    // an updated event for the parent component
                    (response.status == 201) ? this.$emit('updated', response) : '';
                }).catch((error) => {
                    // If the returns an error, fire an update-error
                    // event for the parent component.
                    this.$emit('updateError', error);
                });
            },

            // Create a snapshot of the current state of the workflow
            // in json format.
            createWorkflowSnapshot() {
                this.gojsDiagram = window.laraflowGo.model.toJSON();
            },

            // Catch the fired event and add a new item to the
            // workflow palette with the given data
            addStatus(category, name) {
                // If the new status exist in the diagram
                if (! this.checkDuplications(name) ) {
                    // Fire a duplicate element event to handle the
                    // error on the parent side and return
                    this.$emit('duplicateElement', name);
                    return false;
                }
                // otherwise we add the status to the diagram
                window.laraflowGo.model.addNodeData({category: category, text: name});
            },

            // Enable or disable the visible of the link text
            setLinksTextVisible() {
                this.createWorkflowSnapshot();
                // Cast the json object to array to process the workflow
                var linkDataArray = JSON.parse(this.gojsDiagram);
                // than set all of the visible attribute based on
                // the value of the checkbox
                for(var i in linkDataArray.linkDataArray) {
                    linkDataArray.linkDataArray[i].visible = !this.showTransition;
                }
                // and finally pass to the gojs diagram
                // to refresh it
                this.setGoJsJsonDiagram(JSON.stringify(linkDataArray));
            }
        }
    }
</script>