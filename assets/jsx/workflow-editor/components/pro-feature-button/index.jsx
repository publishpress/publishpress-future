import { __ } from '@wordpress/i18n';
import NodeIcon from '../node-icon';

export default function ProFeatureButton({ link }) {

    const onClick = () => {
        window.open(link, '_blank');
    };

    return (
        <div
            onClick={onClick}
            style={{
                cursor: 'pointer',
                marginLeft: '8px',
                color: '#000',
                backgroundColor: '#ffb200',
                borderRadius: '50%',
                minWidth: '26px',
                minHeight: '26px',
                display: 'inline-block',
                textAlign: 'center',
                boxSizing: 'border-box',
                paddingTop: '5px'
            }}
            title={__("Upgrade to Pro to unlock this feature.", "post-expirator")}
        >
            <NodeIcon icon={'lock'} iconSize={14} />
        </div>
    );
}
