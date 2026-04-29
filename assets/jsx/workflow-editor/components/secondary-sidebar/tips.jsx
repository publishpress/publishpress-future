/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useState } from '@wordpress/element';
import { Tip } from '@wordpress/components';

const globalTips = [
	createInterpolateElement(
		__(
			'Select multiple steps by pressing <kbd>cmd</kbd> and clicking on each step in the workflow.',
			'post-expirator'
		),
		{ kbd: <kbd /> }
	),
	__('Drag steps from the inserter and drop them directly into your workflow for easy customization.', 'post-expirator'),
	__('Simply double-click any step to bring up the settings sidebar for quick adjustments.', 'post-expirator'),
	__('Double-click the workflow pane to increase the zoom level for a closer look.', 'post-expirator'),
	createInterpolateElement(
		__(
			'Hold down the <kbd>shift</kbd> key and double-click the workflow pane to decrease the zoom level.',
			'post-expirator'
		),
		{ kbd: <kbd /> }
	),
	__('Easily add new steps by clicking on a step\'s handle and dragging it. Drop it in the workflow pane to see a floating inserter where you can type and search for the step you need.', 'post-expirator'),
];

export function Tips() {
	const [ randomIndex ] = useState(
		// Disable Reason: I'm not generating an HTML id.
		// eslint-disable-next-line no-restricted-syntax
		Math.floor( Math.random() * globalTips.length )
	);

	return <Tip>{ globalTips[ randomIndex ] }</Tip>;
}
