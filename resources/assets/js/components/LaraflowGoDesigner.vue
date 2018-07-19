<template>
    <div class="container-fluid">

        <create-status-dialog @created="addStatus"></create-status-dialog>

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
                            <div class="col-md-2 border-right" style="height: 600px;" id="workflow-designer-palette"></div>
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

    export default {
        props: [
            'configuration',
            'endpoint'
        ],

        components: {
            createStatusDialog
        },

        data() {
            return {
                id: this.configuration.id,
                name: null,
                category: null,
                myPalette: null,
                defaultCategories: [],
                showTransition: true,
                palette: this.configuration.nodeArray,
                links: this.configuration.linkDataArray,
                gojsDiagram: this.configuration.goJsObject
            }
        },

        mounted() {
            // Have to initialize the workflow first
            laraflowGo.initLaraflowGo('workflow-designer-editor');
            // than we can set the palette
            this.setGoJsPalette();
            // Load workflow diagram from the property
            this.setGoJsJsonDiagram(this.gojsDiagram)
            // Have to listen this event, because we can not
            // access to the attributes from the workflow
            // function directly
            EventBus.$on('setLinkLabel', this.setLinkLabel);

            EventBus.$on('checkDuplications', this.checkDuplications);

            // Listener for the link double click action to open
            // the action dialog
            window.laraflowGo.addDiagramListener("ObjectDoubleClicked", function (e) {
                if (e.subject.part.type.Sb == 'Link') {
                    // TODO have to implement this!
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

            window.laraflowGo.addDiagramListener("ExternalObjectsDropped", function (e) {
                EventBus.$emit('checkDuplications', e);
            });
        },

        methods: {
            checkDuplications(evt) {
                var obj;
                var counter = 1;
                var it = evt.subject.iterator;
                var nodeDataArray = JSON.parse(window.laraflowGo.model.toJson());

                while (it.next()) {
                    var node = it.value;
                    obj = node.part.data.text;
                }

                for(var i in nodeDataArray.nodeDataArray) {
                    if (nodeDataArray.nodeDataArray[i].text == obj) {
                        if(counter > 1) {
                            alert('You can not add a workflow step more than once!');
                            window.laraflowGo.remove(node);
                        }
                        counter++;
                    }
                }

            },

            // Set the label of the created link between two nodes
            // based on the name of the nodes
            setLinkLabel(evt) {
                var nodeDataArray = JSON.parse(window.laraflowGo.model.toJson());
                var fromNode, toNode;

                for(var i in nodeDataArray.nodeDataArray) {
                    if (nodeDataArray.nodeDataArray[i].key == evt.subject.part.data.to)
                        toNode = nodeDataArray.nodeDataArray[i].text;

                    if (nodeDataArray.nodeDataArray[i].key == evt.subject.part.data.from)
                    fromNode = nodeDataArray.nodeDataArray[i].text;
                }

                window.laraflowGo.model.setDataProperty(evt.subject.part.data, 'text', fromNode + ' to ' + toNode);
                this.createWorkflowSnapshot();
            },

            // Set the GoJS palette with the default categories and
            // nodeTemplateMap
            setGoJsPalette() {
                this.setDefaultCategories();

                this.myPalette = GO(go.Palette, "workflow-designer-palette", {
                    nodeTemplateMap: window.laraflowGo.nodeTemplateMap,
                    model: new go.GraphLinksModel(this.defaultCategories),
                    "animationManager.isEnabled": false,
                });
            },

            // Set the default categories based on the given
            // workflow
            setDefaultCategories() {
                for (var i = 0; i < this.palette.length; i++) {
                    this.defaultCategories = this.defaultCategories.concat([{category: this.palette[i].category, text: this.palette[i].text}]);
                }
            },

            // Set the GoJS diagram based on the given json string
            setGoJsJsonDiagram(diagram) {
                window.laraflowGo.model = go.Model.fromJson(diagram);
            },

            // Show the new workflow status modal to add a new
            // item to the workflow palette
            show() {
                EventBus.$emit('showCreateWorkflowStatusModal');
            },

            // Save the current status of the workflow
            save() {
                this.createWorkflowSnapshot();

                axios.put(this.endpoint, {
                    configuration: this.gojsDiagram
                }).then((response) => {
                    if(response.status == 201) {
                        flash(response.data, 'success');
                    }
                }).catch((error) => {
                    console.log(error);
                });
            },

            // Create a snapshot of the current state of the workflow
            createWorkflowSnapshot() {
                this.gojsDiagram = window.laraflowGo.model.toJSON();
            },

            // Catch the fired event and add a new item to the
            // workflow palette with the given data
            addStatus(category, name) {
                // First we have to reset the nodeDataArray
                // except this, can not add a new element
                this.myPalette.model.nodeDataArray = [];
                this.palette.push({category: category, text: name});
                // than set the new array
                this.myPalette.model.nodeDataArray = this.palette;
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