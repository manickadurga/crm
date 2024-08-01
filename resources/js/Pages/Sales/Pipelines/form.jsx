// // import {
// //     Button,
// //     Form,
// //     Input,
// //     Radio,
// //     Select,
// //     Tooltip,
// //     Card,
// //     Space,
// //     DatePicker
// //   } from 'antd';
// //   import React, { useState } from 'react';
// //   import { ArrowLeftOutlined } from "@ant-design/icons";
// //   import { Link } from "react-router-dom";
// //   import  {CKEditor} from '@ckeditor/ckeditor5-react';
// //   import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
// // import Pipelines from '.';
    

// //   const { Option } = Select;
  
// //   const formItemLayout = {
// //     labelCol: {
// //       xs: { span: 28 },
// //       sm: { span: 10 },
// //     },
// //     wrapperCol: {
// //       xs: { span: 24 },
// //       sm: { span: 20 },
// //     },
// //   };
  
// //   const tailFormItemLayout = {
// //     wrapperCol: {
// //       xs: { span: 24, offset: 0 },
// //       sm: { span: 16, offset: 8 },
// //     },
// //   };
  
// //   const gridStyle = {
// //     display: 'grid',
// //     gridTemplateColumns: 'repeat(2, 1fr)',
// //     gap: '16px',
// //   };
  
// //   const gridItemStyle = {
// //     width: '100%',
// //   };
  
// //   const formFields = [
// //     {
// //       title: "Register",
// //       fields: [
// //         {
// //           name: 'author',
// //           label: 'Author',
// //           rules: [
// //             {
// //               required: true,
// //               message: 'Please select a Author!',
// //             },
// //           ],
// //           type: 'dropdown',
// //           options: [
// //             { label: 'All Employees', value: 'allemployees' },
// //             { label: 'Employee 1', value: 'Employee1' },
// //             { label: 'Employee 2', value: 'Employee2' },
// //             { label: 'Employee 3', value: 'Employee3' },
// //           ],
// //           defaultValue: 'All Employees',
// //         },
// //         {
// //           name: 'template',
// //           label: 'Template',
// //           rules: [
// //             {
// //               required: true,
// //               message: 'Please select a Template!',
// //             },
// //           ],
// //           type: 'dropdown',
// //           options: [
// //             { label: 'None', value: 'none' },
// //             { label: 'Template 1', value: 'Template1' },
// //             { label: 'Template 2', value: 'Template2' },
// //             { label: 'Template 3', value: 'Template3' },
// //           ],
// //           defaultValue: 'All Employees',
// //         },
// //         {
// //           name: 'contact',
// //           label: 'Contact',
// //           rules: [
// //             {
// //               required: true,
// //               message: 'Please select a Contact!',
// //             },
// //           ],
// //           type: 'dropdown',
// //           options: [
// //             { label: 'None', value: 'none' },
// //             { label: 'Contact 1', value: 'Contact1' },
// //             { label: 'Contact 2', value: 'Contact2' },
// //             { label: 'Contact 3', value: 'Contact3' },
// //           ],
// //         },
// //         {
// //           name: 'jobposturl',
// //           label: 'Job Post Url',
// //           type: 'text',
// //           rules: [
// //             {
// //               required: true,
// //               message: 'Enter your Job Post Url!',
// //             },
// //           ],
// //         },
// //         {
// //           name: 'proposaldate',
// //           label: 'Proposal Date',
// //           rules: [
// //             {
// //               required: true,
// //               message: 'Please select your Proposal Date!',
// //             },
// //           ],
// //           type: 'datepicker',
// //         },
// //         {
// //           name: "tags",
// //           label: "Tags",
// //           type: "tagfields",
// //           tagOptions: [  
// //             {
// //               label: 'Urgent',
// //               value: 'urgent',
// //             },
// //             {
// //               label: 'Important',
// //               value: 'important',
// //             },
// //             {
// //               label: 'Pending',
// //               value: 'pending',
// //             },
// //             {
// //               label: 'Completed',
// //               value: 'completed',
// //             },
// //             {
// //               label: 'Paid',
// //               value: 'paid',
// //             },
// //           ],
// //           value: ['important', 'pending']
// //         },
     
      
// //       ],
// //     },
   
// //   ];
  
// //   const PipelinesForm = () => {
// //     const [form] = Form.useForm();
// //     const [editorData, setEditorData] = useState('');
// //     const [formValues, setFormValues] = useState({});

// //     const handleEditorChange = (event, editor, fieldName) => {
// //       const data = editor.getData();
// //       setEditorData(data);
// //       // Update values object with the latest content of CKEditor
// //       form.setFieldsValue({ [fieldName]: data });
// //     };
  
// //     const handleEditorFocus = (fieldName) => {
// //       // Clear editorData when editor is focused to prevent conflicts
// //       setEditorData('');
// //       // Set the form field name as the focused field
// //       form.setFieldsValue({ focusedFieldName: fieldName });
// //     };
  
// //     const onFinish = (values) => {
// //       console.log('Received values from form:', values);
// //       // Now you can use values for further processing
// //     };
  
// //     return (
// //       <>
// //         <div style={{display:'flex', alignItems:'center'}}>
// //         <Link to="/tasks/teams">
// //           <Tooltip title="Back" placement="right">
// //             <Button shape="circle" htmlType="button" size='small'>
// //               <ArrowLeftOutlined />
// //             </Button>
// //           </Tooltip>
// //         </Link>
// //         <b style={{ fontSize: '18px', marginLeft: '18px' }}>Proposals</b>
// //         </div>
// //         <Form
// //           {...formItemLayout}
// //           form={form}
// //           name="proposalsform"
// //           onFinish={onFinish}
// //           scrollToFirstError
// //         >
// //           <Card title={formFields[0].title} style={{marginTop:8}}>
// //             <div style={gridStyle}>
// //               {formFields[0].fields.map((field, fieldIndex) => (
// //                 <div key={fieldIndex} style={gridItemStyle}>
// //                   <Form.Item
// //                     name={field.name}
// //                     label={field.label}
// //                     rules={field.rules}
// //                     className="form-item"
// //                   >
// //                     {field.type === 'dropdown' ? (
// //                       <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
// //                         {field.options.map((option, optionIndex) => (
// //                           <Option key={optionIndex} value={option.value}>{option.label}</Option>
// //                         ))}
// //                       </Select>
// //                     ) : field.type === 'radiogroup' ? (
// //                       <Radio.Group
// //                         options={field.options}
// //                         defaultValue={field.options[0].value}
// //                         onChange={handleRadioChange}
// //                       />
// //                     ) : field.type === 'datepicker' ? (
// //                         <DatePicker style={{ width: '100%' }} />
// //                       )  : field.type === 'tagfields' ? (
// //                         <Select
// //                           mode="multiple"
// //                           style={{ width: '100%' }}
// //                           placeholder="Select tags"
// //                           defaultValue={field.value} // Set defaultValue to the array of selected values
// //                           options={field.tagOptions.map((option, index) => ({
// //                             label: (
// //                               <Space>
// //                                 <span aria-label={option.label}  
// //                                   className={`badge-${option.value}`} 
// //                                   style={{display:'block', width:'14px', height:'14px', borderRadius:'50px'}}>
// //                                 </span>
// //                                 {option.label}
// //                               </Space>
// //                             ),
// //                             value: option.value,
// //                           }))}
// //                         />
// //                     ) : (
// //                       <Input
// //                         type={field.type}
// //                         style={{ width: '100%' }}
// //                         defaultValue={field.defaultValue}
// //                       />
// //                     )}
// //                   </Form.Item>
// //                 </div>
// //               ))}
// //             </div>
// //           </Card>
          
// //             <Form.Item style={{display:'flex', justifyContent:'right'}}>
// //             <Button type="primary" htmlType="submit">
// //               Create
// //             </Button>
// //           </Form.Item>
// //         </Form>
// //       </>
// //     );
// //   };
  
// //   export default PipelinesForm;


// import React, { useState } from 'react';
// import { Button, Form, Input, Collapse, Space, Tooltip } from 'antd';
// import { MinusCircleOutlined, PlusOutlined, ArrowLeftOutlined } from '@ant-design/icons';
// import { Link } from 'react-router-dom';

// const { Panel } = Collapse;

// const PipelinesForm = () => {
//   const [form] = Form.useForm();
//   const [sections, setSections] = useState([{ key: Date.now(), entries: [{ key: Date.now() }] }]);

//   const addEntry = (sectionKey) => {
//     setSections(prevSections =>
//       prevSections.map(section =>
//         section.key === sectionKey
//           ? {
//               ...section,
//               entries: [...section.entries, { key: Date.now() }]
//             }
//           : section
//       )
//     );
//   };

//   const removeEntry = (sectionKey, entryKey) => {
//     setSections(prevSections =>
//       prevSections.map(section =>
//         section.key === sectionKey
//           ? {
//               ...section,
//               entries: section.entries.filter(entry => entry.key !== entryKey)
//             }
//           : section
//       )
//     );
//   };

//   const onFinish = (values) => {
//     console.log('Received values from form:', values);
  
//     // Convert the flat values object into a structured array
//     const entries = Object.keys(values).reduce((acc, key) => {
//       const [field, entryKey] = key.split('_');
      
//       if (!acc[entryKey]) {
//         acc[entryKey] = {};
//       }
      
//       acc[entryKey][field] = values[key];
      
//       return acc;
//     }, {});
  
//     // Convert the entries object into an array of objects, omitting the key
//     const formattedEntries = Object.keys(entries).map(entryKey => {
//       const { key, ...rest } = entries[entryKey];
//       return rest;
//     });
  
//     console.log('Formatted Entries:', formattedEntries);
  
//     // Now you can use `formattedEntries` for further processing
//   };
  
//   return (
//     <>
//       <div style={{ display: 'flex', alignItems: 'center' }}>
//         <Link to="/tasks/teams">
//           <Tooltip title="Back" placement="right">
//             <Button shape="circle" htmlType="button" size="small">
//               <ArrowLeftOutlined />
//             </Button>
//           </Tooltip>
//         </Link>
//         <b style={{ fontSize: '18px', marginLeft: '18px' }}>Dynamic Form with Entries</b>
//       </div>
//       <Form
//         form={form}
//         name="dynamic-form"
//         onFinish={onFinish}
//         scrollToFirstError
//         style={{ marginTop: 16 }}
//       >
//         <Collapse accordion>
//           {sections.map((section) => (
//             <Panel header={`Section ${sections.indexOf(section) + 1}`} key={section.key}>
//               {section.entries.map((entry) => (
//                 <div key={entry.key} style={{ marginBottom: '1rem' }}>
//                   <Form.Item
//                     name={`name_${entry.key}`}
//                     label="Name"
//                     rules={[{ required: true, message: 'Please input the Name!' }]}
//                     style={{ marginBottom: 0 }}
//                   >
//                     <Input placeholder="Enter Name" />
//                   </Form.Item>
                 
//                   <Form.Item
//                     name={`description_${entry.key}`}
//                     label="Description"
//                     rules={[{ required: true, message: 'Please input the Description!' }]}
//                     style={{ marginBottom: 0 }}
//                   >
//                     <Input.TextArea placeholder="Enter Description" rows={4} />
//                   </Form.Item>
//                   <Button
//                     type="danger"
//                     onClick={() => removeEntry(section.key, entry.key)}
//                     icon={<MinusCircleOutlined />}
//                     style={{ marginTop: '0.5rem' }}
//                   >
//                     Remove Entry
//                   </Button>
//                 </div>
//               ))}
//               <Button
//                 type="dashed"
//                 onClick={() => addEntry(section.key)}
//                 icon={<PlusOutlined />}
//                 style={{ marginTop: '1rem' }}
//               >
//                 Add Entry
//               </Button>
//             </Panel>
//           ))}
//         </Collapse>
//         <Form.Item style={{ display: 'flex', justifyContent: 'right' }}>
//           <Button type="primary" htmlType="submit">
//             Submit
//           </Button>
//         </Form.Item>
//       </Form>
//     </>
//   );
// };

// export default PipelinesForm;


import {
    Button,
    Form,
    Input,
    Radio,
    Select,
    Tooltip,
    Card,
    Space,
    DatePicker
} from 'antd';
import React, { useState } from 'react';
import { ArrowLeftOutlined } from "@ant-design/icons";
import { Link } from "react-router-dom";
import { CKEditor } from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import Pipelines from '.';

const { Option } = Select;

const formItemLayout = {
    labelCol: {
        xs: { span: 28 },
        sm: { span: 10 },
    },
    wrapperCol: {
        xs: { span: 24 },
        sm: { span: 20 },
    },
};

const tailFormItemLayout = {
    wrapperCol: {
        xs: { span: 24, offset: 0 },
        sm: { span: 16, offset: 8 },
    },
};

const gridStyle = {
    display: 'grid',
    gridTemplateColumns: 'repeat(2, 1fr)',
    gap: '16px',
};

const gridItemStyle = {
    width: '100%',
};

const formFields = [
    {
        title: "Register",
        fields: [
            {
                name: 'author',
                label: 'Author',
                rules: [
                    {
                        required: true,
                        message: 'Please select an Author!',
                    },
                ],
                type: 'dropdown',
                options: [
                    { label: 'All Employees', value: 'allemployees' },
                    { label: 'Employee 1', value: 'Employee1' },
                    { label: 'Employee 2', value: 'Employee2' },
                    { label: 'Employee 3', value: 'Employee3' },
                ],
                defaultValue: 'All Employees',
            },
            {
                name: 'template',
                label: 'Template',
                rules: [
                    {
                        required: true,
                        message: 'Please select a Template!',
                    },
                ],
                type: 'dropdown',
                options: [
                    { label: 'None', value: 'none' },
                    { label: 'Template 1', value: 'Template1' },
                    { label: 'Template 2', value: 'Template2' },
                    { label: 'Template 3', value: 'Template3' },
                ],
                defaultValue: 'None',
            },
            {
                name: 'contact',
                label: 'Contact',
                rules: [
                    {
                        required: true,
                        message: 'Please select a Contact!',
                    },
                ],
                type: 'dropdown',
                options: [
                    { label: 'None', value: 'none' },
                    { label: 'Contact 1', value: 'Contact1' },
                    { label: 'Contact 2', value: 'Contact2' },
                    { label: 'Contact 3', value: 'Contact3' },
                ],
            },
            {
                name: 'jobposturl',
                label: 'Job Post Url',
                type: 'text',
                rules: [
                    {
                        required: true,
                        message: 'Enter your Job Post Url!',
                    },
                ],
            },
            {
                name: 'proposaldate',
                label: 'Proposal Date',
                rules: [
                    {
                        required: true,
                        message: 'Please select your Proposal Date!',
                    },
                ],
                type: 'datepicker',
            },
            {
                name: "tags",
                label: "Tags",
                type: "tagfields",
                tagOptions: [
                    {
                        label: 'Urgent',
                        value: 'urgent',
                    },
                    {
                        label: 'Important',
                        value: 'important',
                    },
                    {
                        label: 'Pending',
                        value: 'pending',
                    },
                    {
                        label: 'Completed',
                        value: 'completed',
                    },
                    {
                        label: 'Paid',
                        value: 'paid',
                    },
                ],
                value: ['important', 'pending']
            },
        ],
    },
];

const PipelinesForm = () => {
    const [form] = Form.useForm();
    const [editorData, setEditorData] = useState('');
    const [formValues, setFormValues] = useState({});

    const handleEditorChange = (event, editor, fieldName) => {
        const data = editor.getData();
        setEditorData(data);
        // Update values object with the latest content of CKEditor
        form.setFieldsValue({ [fieldName]: data });
    };

    const handleEditorFocus = (fieldName) => {
        // Clear editorData when editor is focused to prevent conflicts
        setEditorData('');
        // Set the form field name as the focused field
        form.setFieldsValue({ focusedFieldName: fieldName });
    };

    const onFinish = (values) => {
        console.log('Received values from form:', values);
        // Now you can use values for further processing
    };

    return (
        <>
            <div style={{ display: 'flex', alignItems: 'center' }}>
                <Link to="/tasks/teams">
                    <Tooltip title="Back" placement="right">
                        <Button shape="circle" htmlType="button" size='small'>
                            <ArrowLeftOutlined />
                        </Button>
                    </Tooltip>
                </Link>
                <b style={{ fontSize: '18px', marginLeft: '18px' }}>Proposals</b>
            </div>
            <Form
                {...formItemLayout}
                form={form}
                name="proposalsform"
                onFinish={onFinish}
                scrollToFirstError
            >
                <Card title={formFields[0].title} style={{ marginTop: 8 }}>
                    <div style={gridStyle}>
                        {formFields[0].fields.map((field, fieldIndex) => (
                            <div key={fieldIndex} style={gridItemStyle}>
                                <Form.Item
                                    name={field.name}
                                    label={field.label}
                                    rules={field.rules}
                                    className="form-item"
                                >
                                    {field.type === 'dropdown' ? (
                                        <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
                                            {field.options.map((option, optionIndex) => (
                                                <Option key={optionIndex} value={option.value}>{option.label}</Option>
                                            ))}
                                        </Select>
                                    ) : field.type === 'radiogroup' ? (
                                        <Radio.Group
                                            options={field.options}
                                            defaultValue={field.options[0].value}
                                        />
                                    ) : field.type === 'datepicker' ? (
                                        <DatePicker style={{ width: '100%' }} />
                                    ) : field.type === 'tagfields' ? (
                                        <Select
                                            mode="multiple"
                                            style={{ width: '100%' }}
                                            placeholder="Select tags"
                                            defaultValue={field.value} // Set defaultValue to the array of selected values
                                            options={field.tagOptions.map((option, index) => ({
                                                label: (
                                                    <Space>
                                                        <span aria-label={option.label}
                                                            className={`badge-${option.value}`}
                                                            style={{ display: 'block', width: '14px', height: '14px', borderRadius: '50px' }}>
                                                        </span>
                                                        {option.label}
                                                    </Space>
                                                ),
                                                value: option.value,
                                            }))}
                                        />
                                    ) : (
                                        <Input
                                            type={field.type}
                                            style={{ width: '100%' }}
                                            defaultValue={field.defaultValue}
                                        />
                                    )}
                                </Form.Item>
                            </div>
                        ))}
                    </div>
                </Card>

                <Form.Item style={{ display: 'flex', justifyContent: 'right' }}>
                    <Button type="primary" htmlType="submit">
                        Create
                    </Button>
                </Form.Item>
            </Form>
        </>
    );
};

export default PipelinesForm;
