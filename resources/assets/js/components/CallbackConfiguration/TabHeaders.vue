<template>
	<ul class="nav nav-tabs" role="tablist">
		<li v-for="(tab, index) in tabs" class="nav-item">
			<a class="nav-link" :class="[index === 0 ? 'active' : '']" :id="setTabId(tab)" data-toggle="tab" :href="setTab(tab)" role="tab" aria-controls="tab" :aria-selected="{'true': index === 0}">
                <slot :tab="tab">{{index}}</slot>
            </a>
		</li>
	</ul>
</template>

<script>
	export default {
		props: ['tabs'],

		methods: {
			// Set the tab href for the navigation
			setTab(tab) {
				return '#' + tab.split(' ').join('').toLowerCase();
			},
			// Set the tab id
			setTabId(tab) {
				return tab.split(' ').join('').toLowerCase() + '-tab';
			}
		}
	}
</script>
