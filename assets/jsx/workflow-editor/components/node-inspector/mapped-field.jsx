import PostQuery from "../data-fields/post-query";
import { __, sprintf } from "@wordpress/i18n";
import Schedule from "../data-fields/schedule";
import DebugData from "../data-fields/debug-data";
import RayColor from "../data-fields/ray-color";
import Text from "../data-fields/text";
import TaxonomyTerms from "../data-fields/taxonomy-terms";
import PostStatus from "../data-fields/post-status";
import Textarea from "../data-fields/textarea";
import PostInput from "../data-fields/post-input";
import ManualWorkflowInput from "../data-fields/manual-workflow-input";
import List from "../data-fields/list";
import Conditional from "../data-fields/conditional";
import DebugLevels from "../data-fields/debug-levels";
import ExpressionBuilder from "../data-fields/expression-builder";
import Toggle from "../data-fields/toggle";
import PostData from "../data-fields/post-data";
import DateOffset from "../data-fields/date-offset";
import AskForConfirmation from "../data-fields/ask-confirmation";
import UserQuery from "../data-fields/user-query";
import PostFilter from "../data-fields/post-filter";
import PostSearchQuery from "../data-fields/post-search-query";
import ConditionalDateOffset from "../data-fields/conditional-date-offset";
import ActionArgs from "../data-fields/action-args";
import Integer from "../data-fields/integer";
import CustomOptions from "../data-fields/custom-options";
import { InteractiveCustomOptions } from "../data-fields/interactive-custom-options";
import Select from "../data-fields/select";

export const MappedField = (props) => {
    switch (props.type) {
        case "postQuery":
            return (
                <PostQuery {...props} />
            );
        case "postStatus":
            return (
                <PostStatus {...props} />
            );
        case "taxonomyTerms":
            return (
                <TaxonomyTerms {...props} />
            );
        case "schedule":
            return (
                <Schedule {...props} />
            );
        case "toggle":
            return (
                <Toggle {...props} />
            );
        case "debugData":
            return (
                <DebugData {...props} />
            );
        case "rayColor":
            return (
                <RayColor {...props} />
            );
        case "text":
            return (
                <Text {...props} />
            );
        case "textarea":
            return (
                <Textarea {...props} />
            );
        case "postInput":
            return (
                <PostInput {...props} />
            );
        case "manualWorkflowInput":
            return (
                <ManualWorkflowInput {...props} />
            );
        case "list":
            return (
                <List {...props} />
            );
        case "conditional":
            return (
                <Conditional {...props} />
            );
        case "debugLevels":
            return (
                <DebugLevels {...props} />
            );
        case "expression":
            return (
                <ExpressionBuilder {...props} />
            );
        case "postData":
            return (
                <PostData {...props} />
            );
        case "dateOffset":
            return (
                <DateOffset {...props} />
            );
        case "askForConfirmation":
            return (
                <AskForConfirmation {...props} />
            );
        case "userQuery":
            return (
                <UserQuery {...props} />
            );
        case "postFilter":
            return (
                <PostFilter {...props} />
            );
        case "postSearchQuery":
            return (
                <PostSearchQuery {...props} />
            );
        case "conditionalDateOffset":
            return (
                <ConditionalDateOffset {...props} />
            );
        case "actionArgs":
            return (
                <ActionArgs {...props} />
            );
        case "integer":
            return (
                <Integer {...props} />
            );
        case "customOptions":
            return (
                <CustomOptions {...props} />
            );
        case "interactiveCustomOptions":
            return (
                <InteractiveCustomOptions {...props} />
            );
        case "select":
            return (
                <Select {...props} />
            );
    }

    return (
        <div className="description">
            <i className="dashicons dashicons-warning" />
            {sprintf(__('Field type %s is not implemented', 'publihspress-future-pro'), props.type)}
        </div>
    );
}

export default MappedField;
