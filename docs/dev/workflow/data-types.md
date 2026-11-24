# Data Types

Data Types define the nature and structure of the data that flows through the workflow. Each data type specifies the kind of information a node can handle, ensuring that data is processed correctly and consistently. Some data types are objects and expose internal properties that can be accessed by using `.` as separator: `post.post_title`.

Here is a list of the available data types.

- array
- boolean
- datetime
- email
- input
- integer
- node: ID, name, label, activation_timestamp
- post: ID, post_title, post_content, post_excerpt, post_status, post_type, post_date, post_modified, permalink
- site: name, description, url, home_url, admin_email
- string
- user: ID, user_email, user_login, display_name, roles, caps, user_registered
- workflow: ID, title, description, modified_at
