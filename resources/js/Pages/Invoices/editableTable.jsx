import React, { useState } from 'react';
import { Form, Input, InputNumber, Popconfirm, Table, Typography, Button, Select } from 'antd';

const EditableCell = ({
  editing,
  dataIndex,
  title,
  inputType,
  record,
  index,
  children,
  ...restProps
}) => {
  const inputNode = inputType === 'number' ? <InputNumber /> : <Input />;
  return (
    <td {...restProps}>
      {editing ? (
        <Form.Item
          name={dataIndex}
          style={{
            margin: 0,
          }}
          rules={[
            {
              required: true,
              message: `Please Input ${title}!`,
            },
          ]}
        >
          {inputNode}
        </Form.Item>
      ) : (
        children
      )}
    </td>
  );
};

const EditableTable = () => {
  const formItemLayout = {
    labelCol: {
      xs: { span: 28 },
      sm: { span: 8 },
    },
    wrapperCol: {
      xs: { span: 24 },
      sm: { span: 16 },
    },
  };

  const [form] = Form.useForm();
  const [data, setData] = useState([]);
  const [columns, setColumns] = useState([]);
  const [editingKey, setEditingKey] = useState('');
  const [isAddingRow, setIsAddingRow] = useState(false);
  const [secondSelectOptions, setSecondSelectOptions] = useState([]);
  const [secondSelectLabel, setSecondSelectLabel] = useState('');

  const handleInvType = (value) => {
    setSecondSelectOptions([
      { value: 'jack', label: 'Jack' },
      { value: 'lucy', label: 'Lucy' },
      { value: 'yiminghe', label: 'Yiminghe' },
    ]);
    setSecondSelectLabel('Select by ' + value);
  };

  const handleInvTable = () => {
    const newOriginData = [
      { key: '1', name: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park' },
      { key: '2', name: 'sharp Green', age: 42, location: 'London No. 1 Lake Park' },
      { key: '3', name: 'sharp Black', age: 32, location: 'Sidney No. 1 Lake Park' },
    ];

    const newColumns = Object.keys(newOriginData[0]).map((key) => ({
      title: key,
      dataIndex: key,
      width: '25%',
      editable: true,
    }));

    // Add operation column
    newColumns.push({
      title: 'operation',
      dataIndex: 'operation',
      render: (_, record) => {
        const editable = isEditing(record);
        return editable ? (
          <span>
            <Typography.Link onClick={() => save(record.key)} style={{ marginRight: 8 }}>
              Save
            </Typography.Link>
            <Popconfirm title="Sure to cancel?" onConfirm={cancel}>
              <a>Cancel</a>
            </Popconfirm>
          </span>
        ) : (
          <Typography.Link disabled={editingKey !== ''} onClick={() => edit(record)}>
            Edit
          </Typography.Link>
        );
      },
    });

    setColumns(newColumns);
    setData(newOriginData);
    setIsAddingRow(false);
    var addrowbox = document.getElementById('addRowBox');
    addrowbox.style.display = 'flex';
  };

  const isEditing = (record) => record.key === editingKey;

  const edit = (record) => {
    form.setFieldsValue({
      name: '',
      age: '',
      location: '',
      ...record,
    });
    setEditingKey(record.key);
  };

  const cancel = () => {
    setEditingKey('');
    setIsAddingRow(false);
  };

  const save = async (key) => {
    try {
      const row = await form.validateFields();
      const newData = [...data];
      const index = newData.findIndex((item) => key === item.key);
      if (index > -1) {
        const item = newData[index];
        newData.splice(index, 1, { ...item, ...row });
        setData(newData);
        setEditingKey('');
      } else {
        newData.push(row);
        setData(newData);
        setEditingKey('');
      }
    } catch (errInfo) {
      console.log('Validate Failed:', errInfo);
    }
  };

  const addRow = () => {
    const newRowKey = (data.length + 1).toString();
    const newRecord = { key: newRowKey, name: '', age: '', location: '' };
    setData([...data, newRecord]);
    setEditingKey(newRowKey);
    setIsAddingRow(true);
  };

  const mergedColumns = columns.map((col) => {
    if (!col.editable) {
      return col;
    }
    return {
      ...col,
      onCell: (record) => ({
        record,
        inputType: col.dataIndex === 'age' ? 'number' : 'text',
        dataIndex: col.dataIndex,
        title: col.title,
        editing: isEditing(record),
      }),
    };
  });

  return (
    <>
      <Form form={form} component={false} {...formItemLayout}>
        <div style={{ display: 'flex', width: '100%', gap: 20 }}>
          <Form.Item label='Select an Invoice Type' className="form-item" style={{ width: '50%' }}>
            <Select
              defaultValue="employee"
              style={{ width: '100%' }}
              onChange={handleInvType}
              options={[
                { value: 'employee', label: 'Employee' },
                { value: 'projects', label: 'Projects' },
                { value: 'tasks', label: 'Tasks' },
                { value: 'product', label: 'Products' },
                { value: 'expense', label: 'Expenses' },
              ]}
            />
          </Form.Item>
          <Form.Item label={secondSelectLabel || 'Select by Employee'} className="form-item" style={{ width: '50%' }}>
            <Select
              defaultValue="lucy"
              style={{ width: '100%' }}
              options={secondSelectOptions}
              onChange={handleInvTable}
            />
          </Form.Item>
        </div>
        <div id='addRowBox' style={{ display: 'none', width: '100%', flexWrap: 'wrap', position: 'relative' }}>
          <Table
            components={{
              body: {
                cell: EditableCell,
              },
            }}
            bordered
            dataSource={data}
            columns={mergedColumns}
            rowClassName="editable-row"
            pagination={{
              onChange: cancel,
            }}
            style={{ width: '100%' }}
          />
          <Button onClick={addRow} disabled={isAddingRow} style={{ position: 'absolute', bottom: 16 }}>
            Add Row
          </Button>
        </div>
      </Form>
    </>
  );
};

export default EditableTable;










// import React, { useState } from 'react';
// import { Form, Input, InputNumber, Popconfirm, Table, Typography, Button, Select } from 'antd';

// const EditableCell = ({
//   editing,
//   dataIndex,
//   title,
//   inputType,
//   record,
//   index,
//   children,
//   ...restProps
// }) => {
//   const inputNode = inputType === 'number' ? <InputNumber /> : <Input />;
//   return (
//     <td {...restProps}>
//       {editing ? (
//         <Form.Item
//           name={dataIndex}
//           style={{
//             margin: 0,
//           }}
//           rules={[
//             {
//               required: true,
//               message: `Please Input ${title}!`,
//             },
//           ]}
//         >
//           {inputNode}
//         </Form.Item>
//       ) : (
//         children
//       )}
//     </td>
//   );
// };

// const EditableTable = () => {
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

//   const [form] = Form.useForm();
//   const [data, setData] = useState([]);
//   const [columns, setColumns] = useState([]);
//   const [editingKey, setEditingKey] = useState('');
//   const [isAddingRow, setIsAddingRow] = useState(false);
//   const [secondSelectOptions, setSecondSelectOptions] = useState([]);
//   const [secondSelectLabel, setSecondSelectLabel] = useState('');

//   const handleInvType = (value) => {
//     setSecondSelectOptions([
//       { value: 'jack', label: 'Jack' },
//       { value: 'lucy', label: 'Lucy' },
//       { value: 'yiminghe', label: 'Yiminghe' },
//     ]);
//     setSecondSelectLabel('Select by ' + value);
//   };

//   const handleInvTable = () => {
//     const newOriginData = [
//       { id: '1', label: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park', status: 'active' },
//       { id: '2', label: 'sharp Green', age: 42, location: 'London No. 1 Lake Park', status: 'active' },
//       { id: '3', label: 'sharp Black', age: 32, location: 'Sidney No. 1 Lake Park', status: 'active' },
//     ];
    
//     const newColumns = Object.keys(newOriginData[0]).map((key) => ({
//       title: key,
//       dataIndex: key,
//       width: '25%',
//       editable: true,
//     }));

//     // Add operation column
//     newColumns.push({
//       title: 'operation',
//       dataIndex: 'operation',
//       render: (_, record) => {
//         const editable = isEditing(record);
//         return editable ? (
//           <span>
//             <Typography.Link onClick={() => save(record.key)} style={{ marginRight: 8 }}>
//               Save
//             </Typography.Link>
//             <Popconfirm title="Sure to cancel?" onConfirm={cancel}>
//               <a>Cancel</a>
//             </Popconfirm>
//           </span>
//         ) : (
//           <Typography.Link disabled={editingKey !== ''} onClick={() => edit(record)}>
//             Edit
//           </Typography.Link>
//         );
//       },
//     });

//     setColumns(newColumns);
//     setData(newOriginData);
//     setIsAddingRow(false);
//     var addrowbox = document.getElementById('addRowBox');
//     addrowbox.style.display = 'flex';
//   };

//   const isEditing = (record) => record.key === editingKey;

//   const edit = (record) => {
//     const fieldValues = {};
//     Object.keys(record).forEach((key) => {
//       fieldValues[key] = record[key];
//     });
//     form.setFieldsValue(fieldValues);
//     setEditingKey(record.key);
//   };

//   const cancel = () => {
//     setEditingKey('');
//     setIsAddingRow(false);
//   };

//   const save = async (key) => {
//     try {
//       const row = await form.validateFields();
//       const newData = [...data];
//       const index = newData.findIndex((item) => key === item.key);
//       if (index > -1) {
//         const item = newData[index];
//         newData.splice(index, 1, {
//           ...item,
//           ...row,
//         });
//         setData(newData);
//         setEditingKey('');
//       } else {
//         newData.push(row);
//         setData(newData);
//         setEditingKey('');
//       }
//     } catch (errInfo) {
//       console.log('Validate Failed:', errInfo);
//     }
//   };

//   const addRow = () => {
//     const newRowKey = (data.length + 1).toString();
//     const newRecord = { key: newRowKey, id: '', label: '', age: '', location: '' };
//     setData([...data, newRecord]);
//     setEditingKey(newRowKey);
//     setIsAddingRow(true);
//   };

//   const mergedColumns = columns.map((col) => {
//     if (!col.editable) {
//       return col;
//     }
//     return {
//       ...col,
//       onCell: (record) => ({
//         record,
//         inputType: col.dataIndex === 'age' ? 'number' : 'text',
//         dataIndex: col.dataIndex,
//         title: col.title,
//         editing: isEditing(record),
//       }),
//     };
//   });

//   return (
//     <>
//       <Form form={form} component={false} {...formItemLayout}>
//         <div style={{ display: 'flex', width: '100%', gap: 20 }}>
//           <Form.Item label='Select an Invoice Type' className="form-item" style={{ width: '50%' }}>
//             <Select
//               defaultValue="employee"
//               style={{ width: '100%' }}
//               onChange={handleInvType}
//               options={[
//                 { value: 'employee', label: 'Employee' },
//                 { value: 'projects', label: 'Projects' },
//                 { value: 'tasks', label: 'Tasks' },
//                 { value: 'product', label: 'Products' },
//                 { value: 'expense', label: 'Expenses' },
//               ]}
//             />
//           </Form.Item>
//           <Form.Item label={secondSelectLabel || 'Select by Employee'} className="form-item" style={{ width: '50%' }}>
//             <Select
//               defaultValue="lucy"
//               style={{ width: '100%' }}
//               options={secondSelectOptions}
//               onChange={handleInvTable}
//             />
//           </Form.Item>
//         </div>
//         <div id='addRowBox' style={{ display: 'none', width: '100%', flexWrap: 'wrap', position: 'relative' }}>
//           <Table
//             components={{
//               body: {
//                 cell: EditableCell,
//               },
//             }}
//             bordered
//             dataSource={data}
//             columns={mergedColumns}
//             rowClassName="editable-row"
//             pagination={{
//               onChange: cancel,
//             }}
//             style={{ width: '100%' }}
//           />
//           <Button onClick={addRow} disabled={isAddingRow} style={{ position: 'absolute', bottom: 16 }}>
//             Add Row
//           </Button>
//         </div>
//       </Form>
//     </>
//   );
// };

// export default EditableTable;










// EDIT, SAVE/CANCEL IS NOT WORKING

// import React, { useState } from 'react';
// import { Table, Input, Button, Popconfirm, Form, Typography, Select } from 'antd';

// const EditableCell = ({ editing, dataIndex, title, inputType, record, index, children, ...restProps }) => {
//   const inputNode = inputType === 'number' ? <Input type="number" /> : <Input />;
//   return (
//     <td {...restProps}>
//       {editing ? (
//         <Form.Item
//           name={dataIndex}
//           style={{ margin: 0 }}
//           rules={[
//             {
//               required: true,
//               message: `Please Input ${title}!`,
//             },
//           ]}
//         >
//           {inputNode}
//         </Form.Item>
//       ) : (
//         children
//       )}
//     </td>
//   );
// };

// const EditableTable = () => {
//   const [form] = Form.useForm();
//   const [data, setData] = useState([]);
//   const [columns, setColumns] = useState([]);
//   const [editingKey, setEditingKey] = useState('');
//   const [secondSelectOptions, setSecondSelectOptions] = useState([]);
//   const [secondSelectLabel, setSecondSelectLabel] = useState('');
//   const [isAddingRow, setIsAddingRow] = useState(false);

//   const isEditing = (record) => record.key === editingKey;

//   const edit = (record) => {
//     console.log('for edit')
//     form.setFieldsValue({
//       ...record,
//     });
//     setEditingKey(record.key);
//   };

//   const cancel = () => {
//     setEditingKey('');
//   };

//   const save = async (key) => {
//     try {
//       const row = await form.validateFields();
//       const newData = [...data];
//       const index = newData.findIndex((item) => key === item.key);

//       if (index > -1) {
//         const item = newData[index];
//         newData.splice(index, 1, { ...item, ...row });
//         setData(newData);
//         setEditingKey('');
//       } else {
//         newData.push(row);
//         setData(newData);
//         setEditingKey('');
//       }
//     } catch (errInfo) {
//       console.log('Validate Failed:', errInfo);
//     }
//   };

//   const addRow = () => {
//     const newData = {
//       key: data.length + 1,
//       id: '',
//       label: '',
//       age: '',
//       location: '',
//     };
//     setData([...data, newData]);
//     setIsAddingRow(true);
//   };

//   const handleInvType = (value) => {
//     // Handle Invoice Type change logic
//     setSecondSelectLabel('Selected by '+value);
//     setSecondSelectOptions([
//       { value: 'option1', label: 'Option 1' },
//       { value: 'option2', label: 'Option 2' },
//       { value: 'option3', label: 'Option 3' },
//       { value: 'option4', label: 'Option 4' },
//     ]);
//   };

//   const handleInvTable = () => {
//     const originData = [
//       { id: '1', label: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park', status: 'active' },
//       { id: '2', label: 'sharp Green', age: 42, location: 'London No. 1 Lake Park', status: 'active' },
//       { id: '3', label: 'shpar Black', age: 32, location: 'Sidney No. 1 Lake Park', status: 'inactive' },
//     ];
//     setData(originData);

//     const dynamicColumns = Object.keys(originData[0]).map((key) => ({
//       title: key,
//       dataIndex: key,
//       width: '25%',
//       editable: true,
//     }));

//     dynamicColumns.push({
//       title: 'operation',
//       dataIndex: 'operation',
//       render: (_, record) => {
//         const editable = isEditing(record);
//         return editable ? (
//           <span>
//             <Typography.Link
//               onClick={() => save(record.key)}
//               style={{
//                 marginRight: 8,
//               }}
//             >
//               Save
//             </Typography.Link>
//             <Popconfirm title="Sure to cancel?" onConfirm={cancel}>
//               <a>Cancel</a>
//             </Popconfirm>
//           </span>
//         ) : (
//           <Typography.Link disabled={editingKey !== ''} onClick={() => edit(record)}>
//             Edit
//           </Typography.Link>
//         );
//       },
//     });

//     const mergedColumns = dynamicColumns.map((col) => {
//       if (!col.editable) {
//         return col;
//       }
//       return {
//         ...col,
//         onCell: (record) => ({
//           record,
//           inputType: col.dataIndex === 'age' ? 'number' : 'text',
//           dataIndex: col.dataIndex,
//           title: col.title,
//           editing: isEditing(record),
//         }),
//       };
//     });

//     setColumns(mergedColumns);
    
//     var addrowbox = document.getElementById('addRowBox');
//     addrowbox.style.display = 'flex';
//   };

//   return (
//     <>
//       <Form form={form} component={false}>
//         <div style={{ display: 'flex', width: '100%', gap: 20 }}>
//           <Form.Item label="Select an Invoice Type" className="form-item" style={{ width: '50%' }}>
//             <Select
//               defaultValue="employee"
//               style={{ width: '100%' }}
//               onChange={handleInvType}
//               options={[
//                 { value: 'employee', label: 'Employee' },
//                 { value: 'projects', label: 'Projects' },
//                 { value: 'tasks', label: 'Tasks' },
//                 { value: 'product', label: 'Products' },
//                 { value: 'expense', label: 'Expenses' },
//               ]}
//             />
//           </Form.Item>
//           <Form.Item label={secondSelectLabel || 'Select by Employee'} className="form-item" style={{ width: '50%' }}>
//             <Select
//               defaultValue="lucy"
//               style={{ width: '100%' }}
//               options={secondSelectOptions}
//               onChange={handleInvTable}
//             />
//           </Form.Item>
//         </div>
//         <div id="addRowBox" style={{ display: 'none', width: '100%', flexWrap: 'wrap', position: 'relative' }}>
//           <Table
//             components={{
//               body: {
//                 cell: EditableCell,
//               },
//             }}
//             bordered
//             dataSource={data}
//             columns={columns}
//             rowClassName="editable-row"
//             pagination={{
//               onChange: cancel,
//             }}
//             style={{ width: '100%' }}
//           />
//           <Button onClick={addRow} disabled={isAddingRow} style={{ position: 'absolute', bottom: 16 }}>
//             Add Row
//           </Button>
//         </div>
//       </Form>
//     </>
//   );
// };

// export default EditableTable;




















// WORKING BUT ORIGIN DATA FROM OUTSIDE

// import React, { useState, useEffect } from 'react';
// import { Form, Input, InputNumber, Popconfirm, Table, Typography, Button, Select } from 'antd';



// const EditableCell = ({
//   editing,
//   dataIndex,
//   title,
//   inputType,
//   record,
//   index,
//   children,
//   ...restProps
// }) => {
//   const inputNode = inputType === 'number' ? <InputNumber /> : <Input />;
//   return (
//     <td {...restProps}>
//       {editing ? (
//         <Form.Item
//           name={dataIndex}
//           style={{
//             margin: 0,
//           }}
//           rules={[
//             {
//               required: true,
//               message: `Please Input ${title}!`,
//             },
//           ]}
//         >
//           {inputNode}
//         </Form.Item>
//       ) : (
//         children
//       )}
//     </td>
//   );
// };

//   const EditableTable = () => {
//     const formItemLayout = {
//             labelCol: {
//               xs: {
//                 span: 28,
//               },
//               sm: {
//                 span: 8,
//               },
//             },
//             wrapperCol: {
//               xs: {
//                 span: 24,
//               },
//               sm: {
//                 span: 16,
//               },
//             },
//           };
//     const originData = [
//       { key: '1', name: 'John Brown', age: 32, address: 'New York No. 1 Lake Park' },
//       { key: '2', name: 'Jim Green', age: 42, address: 'London No. 1 Lake Park' },
//       { key: '3', name: 'Joe Black', age: 32, address: 'Sidney No. 1 Lake Park' },
//     ];
//     const [form] = Form.useForm();
//     const [data, setData] = useState(originData);
//     const [editingKey, setEditingKey] = useState('');
//     const [isAddingRow, setIsAddingRow] = useState(false); // New state for tracking add row mode
  
//   const [secondSelectOptions, setSecondSelectOptions] = useState([]);
//   const [secondSelectLabel, setSecondSelectLabel] = useState('');
//   const handleInvType = (value) => {
//     setSecondSelectOptions([
//       { value: 'jack', label: 'Jack' },
//       { value: 'lucy', label: 'Lucy' },
//       { value: 'yiminghe', label: 'Yiminghe' },
//     ]);
//     setSecondSelectLabel('Select by ' + value);
//   };
//   const handleInvTable = () => {
//     const newOriginData = [
//         { id: '1', label: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park' },
//         { id: '2', label: 'sharp Green', age: 42, location: 'London No. 1 Lake Park' },
//         { id: '3', label: 'shpar Black', age: 32, location: 'Sidney No. 1 Lake Park' },
//       ];
//     var addrowbox = document.getElementById('addRowBox');
//     addrowbox.style.display = 'flex';
//   };

//   const columns = Object.keys(originData[0]).map((key) => ({
//     title: key,
//     dataIndex: key,
//     width: '25%',
//     editable: true,
//   }));

//   // Add operation column
//   columns.push({
//     title: 'operation',
//     dataIndex: 'operation',
//     render: (_, record) => {
//       const editable = isEditing(record);
//       return editable ? (
//         <span>
//           <Typography.Link
//             onClick={() => save(record.key)}
//             style={{
//               marginRight: 8,
//             }}
//           >
//             Save
//           </Typography.Link>
//           <Popconfirm title="Sure to cancel?" onConfirm={cancel}>
//             <a>Cancel</a>
//           </Popconfirm>
//         </span>
//       ) : (
//         <Typography.Link disabled={editingKey !== ''} onClick={() => edit(record)}>
//           Edit
//         </Typography.Link>
//       );
//     },
//   });

// const mergedColumns = columns.map((col) => {
//   if (!col.editable) {
//     return col;
//   }
//   return {
//     ...col,
//     onCell: (record) => ({
//       record,
//       inputType: col.dataIndex === 'age' ? 'number' : 'text',
//       dataIndex: col.dataIndex,
//       title: col.title,
//       editing: isEditing(record),
//     }),
//   };
// });

//     const isEditing = (record) => record.key === editingKey;
  
//     const edit = (record) => {
//       const fieldValues = {};
//       Object.keys(record).forEach((key) => {
//         fieldValues[key] = record[key];
//       });
//       form.setFieldsValue(fieldValues);
//       setEditingKey(record.key);
//     };
  
//     const cancel = () => {
//       setEditingKey('');
//       setIsAddingRow(false); // Reset add row mode when canceling
//     };
  
//     const save = async (key) => {
//       try {
//         const row = await form.validateFields();
//         const newData = [...data];
//         const index = newData.findIndex((item) => key === item.key);
//         if (index > -1) {
//           const item = newData[index];
//           newData.splice(index, 1, {
//             ...item,
//             ...row,
//           });
//           setData(newData);
//           setEditingKey('');
//         } else {
//           newData.push(row);
//           setData(newData);
//           setEditingKey('');
//         }
//       } catch (errInfo) {
//         console.log('Validate Failed:', errInfo);
//       }
//     };
  
//     const addRow = () => {
//         const newRowKey = (data.length + 1).toString(); // Generate a new key for the new row
//         const newRecord = { key: newRowKey, name: '', age: '', address: '' }; // Create a new row with empty values
//         setData([...data, newRecord]); // Append the new row to the data array
//         setEditingKey(newRowKey); // Set editingKey to the key of the new row
//       };
      
  
//     return (
//       <> <Form form={form} component={false} 
//           {...formItemLayout}>
//               <div style={{display:'flex', width:'100%',gap:20}}>
//               <Form.Item label='Select an Invoice Type' className="form-item" style={{ width: '50%' }}>
//                   <Select
//                   defaultValue="employee"
//                   style={{ width: '100%' }}
//                   onChange={handleInvType}
//                   options={[
//                       { value: 'employee', label: 'Employee' },
//                       { value: 'projects', label: 'Projects' },
//                       { value: 'tasks', label: 'Tasks' },
//                       { value: 'product', label: 'Products' },
//                       { value: 'expense', label: 'Expenses' },
//                   ]}
//                   />
//               </Form.Item>
//               <Form.Item label={secondSelectLabel || 'Select by Employee'} className="form-item" style={{ width: '50%' }}>
//                   <Select
//                   defaultValue="lucy"
//                   style={{ width: '100%' }}
//                   options={secondSelectOptions}
//                   onChange={handleInvTable}
//                   />
//               </Form.Item>
//               </div>
//               <div id='addRowBox' style={{display:'none', width:'100%', flexWrap:'wrap', position:'relative'}}>
//                 <Table
//             components={{
//               body: {
//                 cell: EditableCell,
//               },
//             }}
//             bordered
//             dataSource={data}
//             columns={mergedColumns}
//             rowClassName="editable-row"
//             pagination={{
//               onChange: cancel,
//             }}
//             style={{width:'100%'}}
//           />
//           <Button onClick={addRow} disabled={isAddingRow} style={{position:'absolute', bottom:16}}>
//             Add Row
//           </Button>
//           </div>
//         </Form>
//       </>
//     );
//   };
  
// export default EditableTable;