<template>
    <div>
        <div class="modal fade"
             id="callback-configuration"
             tabindex="-1"
             role="dialog"
             aria-hidden="true">
            <div class="modal-dialog modal-lg"
                 role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            Callback
                            Configuration</h5>
                        <button type="button"
                                class="close"
                                data-dismiss="modal"
                                aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs"
                            id="myTab"
                            role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active"
                                   id="home-tab"
                                   data-toggle="tab"
                                   href="#home"
                                   role="tab"
                                   aria-controls="home"
                                   aria-selected="true">Validators</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="profile-tab"
                                   data-toggle="tab"
                                   href="#profile"
                                   role="tab"
                                   aria-controls="profile"
                                   aria-selected="false">Pre Functions</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="contact-tab"
                                   data-toggle="tab"
                                   href="#contact"
                                   role="tab"
                                   aria-controls="contact"
                                   aria-selected="false">Post Functions</a>
                            </li>
                        </ul>
                        <div class="tab-content"
                             id="myTabContent">
                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                                <div class="form-row mt-3">
                                    <div class="form-group col-md-5">
                                        <label>Select validator</label>
                                        <select class="form-control" v-if="callbackObjects" v-model="rule">
                                            <option v-bind:value="validator.validator"  v-for="validator in callbackObjects.validators"  v-text="validator.name"></option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Select field</label>
                                        <input type="text" class="form-control" v-model="field" />
                                    </div>
                                    <div class="form-group col-md-1">
                                        <label>Action</label>
                                       <button class="btn btn-primary" @click="addRule">+</button>
                                    </div>
                                </div>

                                <div class="row">
                                    <ul>
                                        <li v-for="rule in rules">{{ Object.keys(rule)[0] }} - {{ rule[Object.keys(rule)[0]] }}</li>
                                    </ul>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                Pre Function
                            </div>
                            <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                                POST function
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                data-dismiss="modal">
                            Close
                        </button>
                        <button type="button"
                                class="btn btn-primary" @click="save">
                            Save
                            changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {EventBus} from '../../event-bus.js';

    export default {
        props: [
            'validators'
        ],

        data() {
            return {
                rules: [],
                name: null,
                rule: null,
                field: null,
                diagram: null,
                category: null,
                callbackObjects: null
            }
        },

        mounted() {
            // Initialize event handler to show the modal if the event has been fired
            EventBus.$on('showConfigureCallbacksModal', this.show);

            this.rules = this.validators;
        },

        methods: {
            // Show the new callback configuration modal
            show(object, callbacks) {
                this.diagram = object;
                this.callbackObjects = callbacks;

                $("#callback-configuration").modal('show');
            },

            // Add new rule to the rule-set array
            addRule() {
                var obj = {};
                obj[this.field] = this.rule;

                this.rules.push(obj);

                this.rule = null;
                this.field = null;
            },

            save() {
                window.laraflowGo.model.setDataProperty(this.diagram.subject.part.data, 'validators', this.rules);
                $("#callback-configuration").modal('hide');

                this.$emit('configured');
            }
        }
    }
</script>
