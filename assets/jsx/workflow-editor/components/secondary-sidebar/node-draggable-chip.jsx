/**
 * WordPress dependencies
 */
import { _n, sprintf } from '@wordpress/i18n';
import { Flex, FlexItem } from '@wordpress/components';
import { dragHandle } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import NodeIcon from '../node-icon';

export default function NodeDraggableChip({ node, icon }) {
	return (
		<div className="block-editor-block-draggable-chip-wrapper">
			<div className="block-editor-block-draggable-chip">
				<Flex
					justify="center"
					className="block-editor-block-draggable-chip__content"
				>
					<FlexItem>
						{icon ? (
							<NodeIcon icon={icon} />
						) : (
							sprintf(
								/* translators: %d: Number of blocks. */
								_n('%d block', '%d blocks', count, 'post-expirator'),
								count
							)
						)}
					</FlexItem>
					<FlexItem>
						{node.label}
					</FlexItem>
				</Flex>
			</div>
		</div>
	);
}
