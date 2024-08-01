import React, { useState } from 'react';
import { Form, Input, InputNumber, Popconfirm, Table, Typography, Button, Select } from 'antd';

const EditableCell = ({ editing, dataIndex, title, inputType, children, ...restProps }) => {
  const inputNode = inputType === 'number' ? <InputNumber /> : <Input />;
  return (
    <td {...restProps}>
      {editing ? (
        <Form.Item
          name={dataIndex}
          style={{ margin: 0 }}
          rules={[{ required: true, message: `Please Input ${title}!` }]}
        >
          {inputNode}
        </Form.Item>
      ) : (
        children
      )}
    </td>
  );
};

const EditableTable = (options) => {
  console.log('Options received:', options);

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
  const [editingKey, setEditingKey] = useState('');
  const [newRowKey, setNewRowKey] = useState(0);
  // const [fristSelectOptions, setfristSelectOptions] = useState(options);
  
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
      { key: '1', name: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park',locations: 'New York No. 1 Lake Park' },
      { key: '2', name: 'sharp Green', age: 42, location: 'London No. 1 Lake Park',locations: 'New York No. 1 Lake Park' },
      { key: '3', name: 'sharp Black', age: 32, location: 'Sidney No. 1 Lake Park',locations: 'New York No. 1 Lake Park' },
    ];
    setData(newOriginData);
    document.getElementById('addRowBox').style.display = 'flex';
  };

  const isEditing = (record) => record.key === editingKey;

  const edit = (record) => {
    form.setFieldsValue({ name: '', age: '', location: '', ...record });
    setEditingKey(record.key);
  };

  const cancel = () => {
    setEditingKey('');
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
    const key = newRowKey.toString();
    const newRow = {
      key,
      name: '',
      age: '',
      location: '',
    };

    setData((prevData) => [...prevData, newRow]);
    setEditingKey(key);
    setNewRowKey(newRowKey + 1);
    form.setFieldsValue(newRow);
  };

  const newOriginData = [
    { key: '1', name: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park',locations: 'New York No. 1 Lake Park' },
    { key: '2', name: 'sharp Green', age: 42, location: 'London No. 1 Lake Park',locations: 'New York No. 1 Lake Park' },
    { key: '3', name: 'sharp Black', age: 32, location: 'Sidney No. 1 Lake Park',locations: 'New York No. 1 Lake Park' },
  ];

  const newColumns = Object.keys(newOriginData[0]).map((key) => ({
    title: key.charAt(0).toUpperCase() + key.slice(1), // Capitalize the first letter of the title
    dataIndex: key,
    width: '25%',
    editable: true,
  }));

  newColumns.push({
    title: 'Operation',
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

  const mergedColumns = newColumns.map((col) => {
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
    <Form form={form} component={false} {...formItemLayout}>
      <div style={{ display: 'flex', width: '100%', gap: 20 }}>
        <Form.Item label='Select an Invoice Type' 
        className="form-item" style={{ width: '50%' }}>
       <Select
         defaultValue="employee"
         style={{ width: '100%' }}
          onChange={handleInvType}
        // options={options.map(option => ({ label: option.label, value: option.value }))} 
        options={options.options}
        />
        </Form.Item>
        <Form.Item label={secondSelectLabel || 'Select by Employee'} 
        className="form-item" style={{ width: '50%' }}>
          <Select
            // defaultValue="lucy"
            mode="multiple"
            style={{ width: '100%' }}
            options={secondSelectOptions}
            
          />
        </Form.Item>
        <Button onClick={handleInvTable}>Generate</Button>
      </div>
      <div id='addRowBox' style={{ display: 'none', width: '100%', flexWrap: 'wrap', position: 'relative' }}>
        <Table
          components={{ body: { cell: EditableCell } }}
          bordered
          dataSource={data}
          columns={mergedColumns}
          rowClassName="editable-row"
          pagination={{ onChange: cancel }}
          style={{ width: '100%' }}
        />
        <Button onClick={addRow} style={{ position: 'absolute', bottom: 16 }}>
          Add Row
        </Button>
      </div>
    </Form>
  );
};

export default EditableTable;


