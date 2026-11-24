## Custom data type options/form in the expression builder

If you need to add custom options/form in the expression builder (like the `post.meta` input for the `post` data type),
you can do so by creating a property in your custom data type like `custom` to the `propertiesSchema` array.

```jsx
export function MyTypeData() {
    return {
        name: "my-type",
        label: "My Type",
        type: "object",
        objectType: "my-type",
        propertiesSchema: [
            {
                name: "custom",
                type: "custom",
                label: "Custom",
                description: "Custom description",
            },
        ],
    };
}
```

Considering as example, if another data type like `post` has a `my-type` property:

```jsx
export function PostData() {
    return {
        name: "post",
        label: "Post",
        type: "object",
        objectType: "post",
        propertiesSchema: [
            ...
            {
                name: "my-type",
                type: "my-type",
                label: "My Type",
                description: "My type description",
            },
        ],
    };
}
```

The `custom` property will be rendered in the expression builder as a custom input for the `my-type` property.

Then in the `ColumnsContainer` component, you can add a `meta` property to the `currentItem` object to render the `custom` property in the expression builder.
