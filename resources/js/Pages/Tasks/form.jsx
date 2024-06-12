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
      title: "Add Tasks",
      fields: [
        {
          name: 'tasksnumber',
          label: 'Tasks Number',
          type: 'number',
          rules: [
            {
              required: true,
              message: 'Enter your Tasks Number!',
            },
          ],
        },
        {
          name: 'projects',
          label: 'Projects',
          rules: [
            {
              required: true,
              message: 'Please select a Project!',
            },
          ],
          type: 'dropdown',
          options: [
            { label: 'None', value: 'none' },
            { label: 'Project 1', value: 'Project1' },
            { label: 'Project 2', value: 'Project2' },
            { label: 'Project 3', value: 'Project3' },
          ],
          defaultValue: 'none',
        },
        {
          name: 'status',
          label: 'Status',
          rules: [
            {
              required: true,
              message: 'Please select a Status!',
            },
          ],
          type: 'dropdown',
          options: [
            { label: 'None', value: 'none' },
            { label: 'Open', value: 'open' },
            { label: 'In Progress', value: 'inprogress' },
            { label: 'In Review', value: 'inreview' },
            { label: 'Completed', value: 'completed' },
            { label: 'Closed', value: 'closed' },
          ],
          defaultValue: 'none',
        },
        {
          name: 'Employee/Teams',
          label: 'Choose any',
          type: 'radiogroup',
          options: [
            {label:"Employee", value:"employee"},
            {label:"Teams", value:"teams"}
            ],
        },
      ],
    },
    {
        title: "Employee Tasks",
        fields: [
            {
                name: "addremoveemployee",
                label: "Add or Remove Employee",
                type: "tagfields",
                tagOptions: [  
                  {
                    label: 'Torrie',
                    value: 'torrie',
                  },
                  {
                    label: 'Joey',
                    value: 'joey',
                  },
                  {
                    label: 'Misissy',
                    value: 'misissy',
                  },
                  {
                    label: 'Chory',
                    value: 'chory',
                  },
                  {
                    label: 'Ausaheb',
                    value: 'ausaheb',
                  },
                ],
              },
              {
                name: 'employeetitle',
                label: 'Employee Title',
                type: 'text',
                rules: [
                  {
                    required: true,
                    message: 'Enter your Tasks Title!',
                  },
                ],
              },
              {
                name: 'employeepriority',
                label: 'Employee Priority',
                rules: [
                  {
                    required: true,
                    message: 'Please select a Prioriy!',
                  },
                ],
                type: 'dropdown',
                options: [
                  { label: 'None', value: 'none' },
                  { label: 'Urgent', value: 'urgent' },
                  { label: 'Incomplete', value: 'incomplete' },
                  { label: 'Closed', value: 'closed' },
                ],
                defaultValue: 'none',
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
              {
                name: 'duedate',
                label: 'Due Date',
                rules: [
                  {
                    required: true,
                    message: 'Please select your Due Date!',
                  },
                ],
                type: 'datepicker',
              },
              {
                name: 'estimate',
                label: 'Estimate',
                rules: [
                  {
                    required: true,
                    message: 'Please select your Due Date!',
                  },
                ],
                type: 'dayshrsmins',
                fields: [
                    {name:'days', type:'number'},
                    {name:'hours', type:'number'},
                    {name:'minutes', type:'number'},
                ]
              },
          {
            name: 'employeetasksnumber',
            label: 'Employee Tasks Number',
            type: 'number',
            rules: [
              {
                required: true,
                message: 'Enter your Tasks Number!',
              },
            ],
          },
          {
            name: 'employeeprojects',
            label: 'Employee Projects',
            rules: [
              {
                required: true,
                message: 'Please select a Project!',
              },
            ],
            type: 'dropdown',
            options: [
              { label: 'None', value: 'none' },
              { label: 'Project 1', value: 'Project1' },
              { label: 'Project 2', value: 'Project2' },
              { label: 'Project 3', value: 'Project3' },
            ],
            defaultValue: 'none',
          },
          {
            name: 'employeestatus',
            label: 'Employee Status',
            rules: [
              {
                required: true,
                message: 'Please select a Status!',
              },
            ],
            type: 'dropdown',
            options: [
              { label: 'None', value: 'none' },
              { label: 'Open', value: 'open' },
              { label: 'In Progress', value: 'inprogress' },
              { label: 'In Review', value: 'inreview' },
              { label: 'Completed', value: 'completed' },
              { label: 'Closed', value: 'closed' },
            ],
            defaultValue: 'none',
          },
        ],
    },
    {
        title: "Teams Tasks",
        fields: [
            {
                name: "addremoveteams",
                label: "Add or Remove Teams",
                type: "tagfields",
                tagOptions: [  
                  {
                    label: 'Gryffindor',
                    value: 'gryffindor',
                  },
                  {
                    label: 'Slytherien',
                    value: 'slytherien',
                  },
                  {
                    label: 'Hufflepuff',
                    value: 'hufflepuff',
                  },
                  {
                    label: 'Ravenclaw',
                    value: 'ravenclaw',
                  },
                ],
              },
              {
                name: 'teamstitle',
                label: 'Employee Title',
                type: 'text',
                rules: [
                  {
                    required: true,
                    message: 'Enter your Tasks Title!',
                  },
                ],
              },
              {
                name: 'teamspriority',
                label: 'Teams Priority',
                rules: [
                  {
                    required: true,
                    message: 'Please select a Prioriy!',
                  },
                ],
                type: 'dropdown',
                options: [
                  { label: 'None', value: 'none' },
                  { label: 'Urgent', value: 'urgent' },
                  { label: 'Incomplete', value: 'incomplete' },
                  { label: 'Closed', value: 'closed' },
                ],
                defaultValue: 'none',
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
              {
                name: 'duedate',
                label: 'Due Date',
                rules: [
                  {
                    required: true,
                    message: 'Please select your Due Date!',
                  },
                ],
                type: 'datepicker',
              },
              {
                name: 'estimate',
                label: 'Estimate',
                rules: [
                  {
                    required: true,
                    message: 'Please select your Due Date!',
                  },
                ],
                type: 'dayshrsmins',
                fields: [
                    {name:'days', type:'number'},
                    {name:'hours', type:'number'},
                    {name:'minutes', type:'number'},
                ]
              },
          {
            name: 'teamstasksnumber',
            label: 'Teams Tasks Number',
            type: 'number',
            rules: [
              {
                required: true,
                message: 'Enter your Tasks Number!',
              },
            ],
          },
          {
            name: 'teamsprojects',
            label: 'Teams Projects',
            rules: [
              {
                required: true,
                message: 'Please select a Project!',
              },
            ],
            type: 'dropdown',
            options: [
              { label: 'None', value: 'none' },
              { label: 'Project 1', value: 'Project1' },
              { label: 'Project 2', value: 'Project2' },
              { label: 'Project 3', value: 'Project3' },
            ],
            defaultValue: 'none',
          },
          {
            name: 'teamsstatus',
            label: 'Teams Status',
            rules: [
              {
                required: true,
                message: 'Please select a Status!',
              },
            ],
            type: 'dropdown',
            options: [
              { label: 'None', value: 'none' },
              { label: 'Open', value: 'open' },
              { label: 'In Progress', value: 'inprogress' },
              { label: 'In Review', value: 'inreview' },
              { label: 'Completed', value: 'completed' },
              { label: 'Closed', value: 'closed' },
            ],
            defaultValue: 'none',
          },
        ],
    },
  ];
  
  const TasksForm = () => {
    const [form] = Form.useForm();
    const [selectedOption, setSelectedOption] = useState('employee');
  
    const handleRadioChange = (e) => {
      setSelectedOption(e.target.value);
    };
  
    const onFinish = (values) => {
      console.log('Received values from form:', values);
    };
  
    return (
      <>
        <div style={{display:'flex', alignItems:'center'}}>
        <Link to="/tasks">
          <Tooltip title="Back" placement="right">
            <Button shape="circle" htmlType="button" size='small'>
              <ArrowLeftOutlined />
            </Button>
          </Tooltip>
        </Link>
        <b style={{ fontSize: '18px', marginLeft: '18px' }}>Tasks</b>
        </div>
        <Form
          {...formItemLayout}
          form={form}
          name="tasksform"
          onFinish={onFinish}
          scrollToFirstError
        >
          <Card title={formFields[0].title} style={{marginTop:8}}>
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
                        onChange={handleRadioChange}
                      />
                    ) : field.type === 'datepicker' ? (
                        <DatePicker style={{ width: '100%' }} />
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
  
          {selectedOption && (
            <Card title={selectedOption} style={{textTransform:'capitalize', marginTop:8, marginBottom:8}}>
              <div style={gridStyle}>
                {/* {formFields.find(field => field.title === selectedOption)?.fields.map((field, fieldIndex) => ( */}
                {formFields.find(field => field.title.toLowerCase().includes(selectedOption))?.fields.map((field, fieldIndex) => (
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
                                  style={{display:'block', width:'14px', height:'14px', borderRadius:'50px'}}>
                                </span>
                                {option.label}
                              </Space>
                            ),
                            value: option.value,
                          }))}
                        />
                    ) : field.type === 'datepicker' ? (
                        <DatePicker style={{ width: '100%' }} />
                    ) : field.type === 'dayshrsmins' ? (
                        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                        {field.fields.map((subField, subFieldIndex) => (
                          <Form.Item
                            key={subFieldIndex}
                            name={subField.name}
                            style={{ width: '30%', margin:0 }}
                          >
                            <Input
                              type='number'
                              placeholder={subField.name.charAt(0).toUpperCase() + subField.name.slice(1)}
                            />
                            <i style={{fontSize:11, float:'right', color:'#888'}}>{subField.name}</i>
                          </Form.Item>
                        ))}
                      </div>
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
          )}
  
          <Form.Item style={{display:'flex', justifyContent:'right'}}>
            <Button type="primary" htmlType="submit">
              Create
            </Button>
          </Form.Item>
        </Form>
      </>
    );
  };
  
  export default TasksForm;
  









// import {
//     Button,
//     Form,
//     Input,
//     Radio,
//     Select,
//     Tooltip
//   } from 'antd';
//   import React, { useState } from 'react';
//   import { ArrowLeftOutlined } from "@ant-design/icons";
//   import { Link } from "react-router-dom";
  
//   const { Option } = Select;
  
//   const formItemLayout = {
//     labelCol: {
//       xs: { span: 28 },
//       sm: { span: 8 },
//     },
//     wrapperCol: {
//       xs: { span: 24 },
//       sm: { span: 16 },
//     },
//   };
  
//   const tailFormItemLayout = {
//     wrapperCol: {
//       xs: { span: 24, offset: 0 },
//       sm: { span: 16, offset: 8 },
//     },
//   };
  
//   const gridStyle = {
//     display: 'grid',
//     gridTemplateColumns: 'repeat(2, 1fr)',
//     gap: '16px',
//   };
  
//   const gridItemStyle = {
//     width: '100%',
//   };
  
//   const formFields = [
//     {
//       title: "Add Tasks",
//       fields: [
//         {
//           name: 'tasksnumber',
//           label: 'Tasks Number',
//           type: 'number',
//           rules: [
//             {
//               required: true,
//               message: 'Enter your Tasks Number!',
//             },
//           ],
//         },
//         {
//           name: 'projects',
//           label: 'Projects',
//           rules: [
//             {
//               required: true,
//               message: 'Please select a Project!',
//             },
//           ],
//           type: 'dropdown',
//           options: [
//             { label: 'None', value: 'none' },
//             { label: 'Project 1', value: 'Project1' },
//             { label: 'Project 2', value: 'Project2' },
//             { label: 'Project 3', value: 'Project3' },
//           ],
//           defaultValue: 'none',
//         },
//         {
//           name: 'status',
//           label: 'Status',
//           rules: [
//             {
//               required: true,
//               message: 'Please select a Status!',
//             },
//           ],
//           type: 'dropdown',
//           options: [
//             { label: 'None', value: 'none' },
//             { label: 'Open', value: 'open' },
//             { label: 'In Progress', value: 'inprogress' },
//             { label: 'In Review', value: 'inreview' },
//             { label: 'Completed', value: 'completed' },
//             { label: 'Closed', value: 'closed' },
//           ],
//           defaultValue: 'none',
//         },
//         {
//           name: 'Employee/Teams',
//           label: 'Choose any',
//           type: 'radiogroup',
//           options: [
//             { label: 'Employee', value: 'employee' },
//             { label: 'Teams', value: 'teams' },
//           ],
//         },
//       ],
//     },
//     {
//       title: "Employee Tasks",
//       name: "employeetasks",
//       fields: [
//         {
//           name: 'tasksnumber',
//           label: 'Tasks Number',
//           type: 'number',
//           rules: [
//             {
//               required: true,
//               message: 'Enter your Tasks Number!',
//             },
//           ],
//         },
//         {
//           name: 'projects',
//           label: 'Projects',
//           rules: [
//             {
//               required: true,
//               message: 'Please select a Project!',
//             },
//           ],
//           type: 'dropdown',
//           options: [
//             { label: 'None', value: 'none' },
//             { label: 'Project 1', value: 'Project1' },
//             { label: 'Project 2', value: 'Project2' },
//             { label: 'Project 3', value: 'Project3' },
//           ],
//           defaultValue: 'none',
//         },
//         {
//           name: 'status',
//           label: 'Status',
//           rules: [
//             {
//               required: true,
//               message: 'Please select a Status!',
//             },
//           ],
//           type: 'dropdown',
//           options: [
//             { label: 'None', value: 'none' },
//             { label: 'Open', value: 'open' },
//             { label: 'In Progress', value: 'inprogress' },
//             { label: 'In Review', value: 'inreview' },
//             { label: 'Completed', value: 'completed' },
//             { label: 'Closed', value: 'closed' },
//           ],
//           defaultValue: 'none',
//         },
//       ],
//     },
//     {
//       title: "Teams Tasks",
//       name: "teamstasks",
//       fields: [
//         {
//           name: 'teamstasksnumber',
//           label: 'Teams Tasks Number',
//           type: 'number',
//           rules: [
//             {
//               required: true,
//               message: 'Enter your Tasks Number!',
//             },
//           ],
//         },
//         {
//           name: 'projects',
//           label: 'Teams Projects',
//           rules: [
//             {
//               required: true,
//               message: 'Please select a Project!',
//             },
//           ],
//           type: 'dropdown',
//           options: [
//             { label: 'None', value: 'none' },
//             { label: 'Project 1', value: 'Project1' },
//             { label: 'Project 2', value: 'Project2' },
//             { label: 'Project 3', value: 'Project3' },
//           ],
//           defaultValue: 'none',
//         },
//         {
//           name: 'status',
//           label: 'Teams Status',
//           rules: [
//             {
//               required: true,
//               message: 'Please select a Status!',
//             },
//           ],
//           type: 'dropdown',
//           options: [
//             { label: 'None', value: 'none' },
//             { label: 'Open', value: 'open' },
//             { label: 'In Progress', value: 'inprogress' },
//             { label: 'In Review', value: 'inreview' },
//             { label: 'Completed', value: 'completed' },
//             { label: 'Closed', value: 'closed' },
//           ],
//           defaultValue: 'none',
//         },
//       ],
//     },
//   ];
  
//   const TasksForm = () => {
//     const [form] = Form.useForm();
//     const [selectedOption, setSelectedOption] = useState('employee');
//     const [autoCompleteResult, setAutoCompleteResult] = useState([]);
  
//     const onWebsiteChange = (value) => {
//       if (!value) {
//         setAutoCompleteResult([]);
//       } else {
//         setAutoCompleteResult(['.com', '.org', '.net', '.ai', '.in', '.co.in'].map((domain) => `${value}${domain}`));
//       }
//     };
  
//     const handleRadioChange = (e) => {
//       setSelectedOption(e.target.value);
//     };
  
//     const onFinish = (values) => {
//       console.log('Received values from form:', values);
//       // Perform further actions here, such as sending values to a server
//     };
  
//     return (
//       <>
//         <Link to="/">
//           <Tooltip title="Back" placement="right">
//             <Button shape="circle" htmlType="button">
//               <ArrowLeftOutlined />
//             </Button>
//           </Tooltip>
//         </Link>
//         <Form
//           {...formItemLayout}
//           form={form}
//           name="tasksform"
//           onFinish={onFinish}
//           scrollToFirstError
//         >
//           <div>
//             <h3>{formFields[0].title}</h3>
//             <div style={gridStyle}>
//               {formFields[0].fields.map((field, fieldIndex) => (
//                 <div key={fieldIndex} style={gridItemStyle}>
//                   <Form.Item
//                     name={field.name}
//                     label={field.label}
//                     rules={field.rules}
//                     colon={false}
//                     className="form-item"
//                   >
//                     {field.type === 'dropdown' ? (
//                       <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
//                         {field.options.map((option, optionIndex) => (
//                           <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                         ))}
//                       </Select>
//                     ) : field.type === 'radiogroup' ? (
//                       <Radio.Group
//                         options={field.options}
//                         defaultValue={field.options[0].value}
//                         onChange={handleRadioChange}
//                       />
//                     ) : (
//                       <Input
//                         type={field.type}
//                         style={{ width: '100%' }}
//                         defaultValue={field.defaultValue}
//                       />
//                     )}
//                   </Form.Item>
//                 </div>
//               ))}
//             </div>
  
//             {selectedOption === 'employee' && (
//               <div>
//                 <h3>{formFields[1].title}</h3>
//                 <div style={gridStyle}>
//                   {formFields[1].fields.map((field, fieldIndex) => (
//                     <div key={fieldIndex} style={gridItemStyle}>
//                       <Form.Item
//                         name={field.name}
//                         label={field.label}
//                         rules={field.rules}
//                         className="form-item"
//                       >
//                         {field.type === 'dropdown' ? (
//                           <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
//                             {field.options.map((option, optionIndex) => (
//                               <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                             ))}
//                           </Select>
//                         ) : (
//                           <Input
//                             type={field.type}
//                             style={{ width: '100%' }}
//                             defaultValue={field.defaultValue}
//                           />
//                         )}
//                       </Form.Item>
//                     </div>
//                   ))}
//                 </div>
//               </div>
//             )}
  
//             {selectedOption === 'teams' && (
//               <div>
//                 <h3>{formFields[2].title}</h3>
//                 <div style={gridStyle}>
//                   {formFields[2].fields.map((field, fieldIndex) => (
//                     <div key={fieldIndex} style={gridItemStyle}>
//                       <Form.Item
//                         name={field.name}
//                         label={field.label}
//                         rules={field.rules}
//                         className="form-item"
//                       >
//                         {field.type === 'dropdown' ? (
//                           <Select style={{ width: '100%' }} defaultValue={field.defaultValue}>
//                             {field.options.map((option, optionIndex) => (
//                               <Select.Option key={optionIndex} value={option.value}>{option.label}</Select.Option>
//                             ))}
//                           </Select>
//                         ) : (
//                           <Input
//                             type={field.type}
//                             style={{ width: '100%' }}
//                             defaultValue={field.defaultValue}
//                           />
//                         )}
//                       </Form.Item>
//                     </div>
//                   ))}
//                 </div>
//               </div>
//             )}
//           </div>
//           <Form.Item {...tailFormItemLayout}>
//             <Button type="primary" htmlType="submit">
//               Create
//             </Button>
//           </Form.Item>
//         </Form>
//       </>
//     );
//   };
  
//   export default TasksForm;