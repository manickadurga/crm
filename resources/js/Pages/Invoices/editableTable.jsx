import React, { useState } from 'react';
import { Form, Input, InputNumber, Popconfirm, Table, Typography, Button, Select } from 'antd';

const EditableCell = ({ editing, dataIndex, title, inputType, record, index, children, ...restProps }) => {
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
  const [form] = Form.useForm();
  const [data, setData] = useState([]);
  const [columns, setColumns] = useState([]);
  const [editingKey, setEditingKey] = useState('');
  const [isAddingRow, setIsAddingRow] = useState(false);
  const [secondSelectOptions, setSecondSelectOptions] = useState([]);
  const [secondSelectLabel, setSecondSelectLabel] = useState('');
  const [firstSelectValue, setFirstSelectValue] = useState('');

  const options = [
    { value: 'employee', label: 'Employee' },
    { value: 'projects', label: 'Projects' },
    { value: 'tasks', label: 'Tasks' },
    { value: 'product', label: 'Products' },
    { value: 'expense', label: 'Expenses' },
  ];

  const handleInvType = (value) => {
    setFirstSelectValue(value);
    let newOptions = [];
    switch (value) {
      case 'employee':
        newOptions = [
          
          { value: 'jack', label: 'Jack' },
          { value: 'lucy', label: 'Lucy' },
          { value: 'yiminghe', label: 'Yiminghe' },
        ];
        break;
      case 'projects':
        newOptions = [
          { value: 'project1', label: 'Project 1' },
          { value: 'project2', label: 'Project 2' },
          { value: 'project3', label: 'Project 3' },
        ];
        break;
      case 'tasks':
        newOptions = [
          { value: 'task1', label: 'Task 1' },
          { value: 'task2', label: 'Task 2' },
          { value: 'task3', label: 'Task 3' },
        ];
        break;
      case 'product':
        newOptions = [
          { value: 'product1', label: 'Product 1' },
          { value: 'product2', label: 'Product 2' },
          { value: 'product3', label: 'Product 3' },
        ];
        break;
      case 'expense':
        newOptions = [
          { value: 'expense1', label: 'Expense 1' },
          { value: 'expense2', label: 'Expense 2' },
          { value: 'expense3', label: 'Expense 3' },
        ];
        break;
      default:
        newOptions = [];
    }
    setSecondSelectOptions(newOptions);
    setSecondSelectLabel('Select by ' + value.charAt(0).toUpperCase() + value.slice(1));
    // setSecondSelectLabel('Select by ' + value);
  };

  const handleInvTable = () => {
    const newOriginData = [
      { key: '1', label: 'sharp Brown', age: 32, location: 'New York No. 1 Lake Park', status: 'active' },
      { key: '2', label: 'sharp Green', age: 42, location: 'London No. 1 Lake Park', status: 'active' },
      { key: '3', label: 'sharp Black', age: 32, location: 'Sidney No. 1 Lake Park', status: 'active' },
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
    document.getElementById('addRowBox').style.display = 'flex';
  };

  const isEditing = (record) => record.key === editingKey;

  const edit = (record) => {
    form.setFieldsValue(record);
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
        newData.splice(index, 1, {
          ...item,
          ...row,
        });
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
    const newRecord = { key: newRowKey, id: '', label: '', age: '', location: '' };
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
      <Form form={form} component={false}>
        <div style={{ display: 'flex', width: '100%', gap: 20 }}>
          <Form.Item label="Select an Invoice Type" className="form-item" style={{ width: '50%' }}>
            <Select
              value={firstSelectValue}
              onChange={handleInvType}
              style={{ width: '100%' }}
              placeholder="Invoice Type"
            >
              <Select.Option value="">Invoice Type</Select.Option>
              {options.map((option) => (
                <Select.Option key={option.value} value={option.value}>
                  {option.label}
                </Select.Option>
              ))}
            </Select>
          </Form.Item>
          {firstSelectValue && (
            <Form.Item label={secondSelectLabel || 'Select by Employee'} className="form-item" style={{ width: '50%' }}>
              <Select
                placeholder={secondSelectLabel}
                style={{ width: '100%' }}
                options={secondSelectOptions}
                onChange={handleInvTable}
              />
            </Form.Item>
          )}
        </div>
        <div id="addRowBox" style={{ display: 'none', width: '100%', flexWrap: 'wrap', position: 'relative' }}>
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













