import { __ } from '@wordpress/i18n';

import { FieldExpressionBuilder } from './field-expression-builder';
import { ValueExpressionBuilder } from './value-expression-builder';
import { withConditional } from './conditional-hoc';

const Conditional = withConditional({
    FieldComponent: FieldExpressionBuilder,
    ValueComponent: ValueExpressionBuilder,
    isProFeature: true,
    defaultField: '{{global.user.id}}'
});

export default Conditional;
