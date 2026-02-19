const NODE_ENV = process.env.NODE_ENV || 'development';
const path = require("path");
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
    mode: NODE_ENV,
    devtool: NODE_ENV === 'development' ? 'source-map' : false,
    entry: {
        i18n: "./assets/jsx/i18n.jsx",
        settingsPostTypes: "./assets/jsx/settings-post-types.jsx",
        settingsGeneral: "./assets/jsx/settings-general.jsx",
        blockEditor: "./assets/jsx/block-editor.jsx",
        classicEditor: "./assets/jsx/classic-editor.jsx",
        quickEdit: "./assets/jsx/quick-edit.jsx",
        bulkEdit: "./assets/jsx/bulk-edit.jsx",
        settingsAdvanced: "./assets/jsx/settings-advanced.jsx",
        settingsDebug: "./assets/jsx/settings-debug.jsx",
        workflowEditor: "./assets/jsx/workflow-editor/index.jsx",
        legacyAction: "./assets/jsx/legacy-action/index.jsx",
        workflowManualSelectionQuickEdit: "./assets/jsx/workflow-manual-selection/quick-edit/index.jsx",
        workflowManualSelectionClassicEditor: "./assets/jsx/workflow-manual-selection/classic-editor/index.jsx",
        workflowManualSelectionBlockEditor: "./assets/jsx/workflow-manual-selection/block-editor/index.jsx",
        workflowManualSelectionBulkEdit: "./assets/jsx/workflow-manual-selection/bulk-edit/index.jsx",
        futureActions: "./assets/jsx/future-actions.jsx",
        backupPanel: "./assets/jsx/backup-panel/index.jsx",
        workflowsListCancelAction: "./assets/jsx/workflows-list/cancel-action/index.jsx",
        workflowsListStatusChange: "./assets/jsx/workflows-list/status-change/index.jsx",
        workflowsListCopyWorkflow: "./assets/jsx/workflows-list/copy-workflow/index.jsx"
    },
    output: {
        path: path.join(__dirname, "assets", "js"),
        filename: NODE_ENV === 'production' ? "[name].min.js" : "[name].js"
    },
    resolve: {
        extensions: ['.js', '.jsx']
    },
    module: {
        rules: [
            {
                test: /\.(jsx)$/, // Identifies which file or files should be transformed.
                use: {loader: "babel-loader"}, //
                exclude: [
                    /(node_modules|bower_components)/,
                ]// JavaScript files to be ignored.
            },
            {
                test: /\.css$/i,
                use: ["style-loader", "css-loader", "postcss-loader"],
            }
        ]
    },
    optimization: {
        minimize: NODE_ENV === 'production',
    },
    // plugins: [
    //     new BundleAnalyzerPlugin({
    //         analyzerMode: 'static',
    //         reportFilename: 'webpack-bundle-stats.html',
    //     }),
    // ],
    externals: {
        "react": "React",
        "react-dom": "ReactDOM",
        "@wordpress/element": "wp.element",
        "@wordpress/components": "wp.components",
        "@wordpress/data": "wp.data",
        "@wordpress/plugins": "wp.plugins",
        "@wordpress/hooks": "wp.hooks",
        "@wordpress/url": "wp.url",
        "@wordpress/i18n": "wp.i18n",
        'wp': 'wp',
        '@publishpress/i18n': 'publishpress.i18n'
    },
};
