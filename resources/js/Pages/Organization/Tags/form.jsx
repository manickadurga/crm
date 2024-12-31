import {
    AutoComplete,
    Button,
    Cascader,
    Checkbox,
    Col,
    Form,
    Input,
    InputNumber,
    Row,
    Select,
    Alert,
    DatePicker,
    Dropdown,
    Steps,
    Space,
    Tooltip,
    Table,
} from "antd";
import React, { useState } from "react";
import {
    SearchOutlined,
    ArrowLeftOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import { Link } from "react-router-dom";
//   import EditableTable from './editableTable';
import { useParams } from "react-router-dom";
//   import { Button, Form, Input, Select, Checkbox, DatePicker, message } from 'antd';
import MyEditor from "../../../Components/ReactQuillTextEditer";

const { Option } = Select;
const residences = [
    {
        value: "zhejiang",
        label: "Zhejiang",
        children: [
            {
                value: "hangzhou",
                label: "Hangzhou",
                children: [
                    {
                        value: "xihu",
                        label: "West Lake",
                    },
                ],
            },
        ],
    },
    {
        value: "jiangsu",
        label: "Jiangsu",
        children: [
            {
                value: "nanjing",
                label: "Nanjing",
                children: [
                    {
                        value: "zhonghuamen",
                        label: "Zhong Hua Men",
                    },
                ],
            },
        ],
    },
];
const formItemLayout = {
    labelCol: {
        xs: {
            span: 28,
        },
        sm: {
            span: 8,
        },
    },
    wrapperCol: {
        xs: {
            span: 24,
        },
        sm: {
            span: 16,
        },
    },
};
const tailFormItemLayout = {
    wrapperCol: {
        xs: {
            span: 24,
            offset: 0,
        },
        sm: {
            span: 16,
            offset: 8,
        },
    },
};

const gridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(2, 1fr)",
    gap: "16px",
};

const gridItemStyle = {
    width: "100%",
};

const formFields = [
    {
        title: "Add tags",
        fields: [
            {
                name: "Tags",
                label: "Tags",
                type: "tagfields",
                tagOptions: [
                    {
                        label: "Completed",
                        value: "completed",
                    },
                    {
                        label: "Paid",
                        value: "paid",
                    },
                ],
            },

            {
                name: "tags color",
                label: "Tags Color",
                type: "tagfields",
                tagOptions: [
                    {
                        label: "RED",
                        value: "red",
                    },
                    {
                        label: "WHITE",
                        value: "white",
                    },
                    {
                        label: "GREY",
                        value: "grey",
                    },
                    {
                        label: "GREEN",
                        value: "green",
                    },
                    {
                        label: "BLUE",
                        value: "blue",
                    },
                ],
            },
            {
                name: "Tenant level",
                label: "Tenant level",
                type: "checkbox",
                defaultValue: false,
            },
            {
                name: "description",
                label: "Description",
                type: "textarea",
            },
        ],
    },
];

const options = [
    { value: "employee", label: "Employee" },
    { value: "projects", label: "Projects" },
    { value: "tasks", label: "Tasks" },
    { value: "product", label: "Products" },
    { value: "expense", label: "Expenses" },
];

const steps = formFields.map((section, sectionIndex) => (
    <div key={section.title}>
        <h3>{section.title}</h3>
        <div style={gridStyle}>
            {section.fields.map((field, fieldIndex) => (
                <div key={fieldIndex} style={gridItemStyle}>
                    <Form.Item
                        name={field.name}
                        label={field.label}
                        rules={field.rules}
                        className="form-item"
                        // {...(field.type !== 'checkbox' ? {label: field.label } : {})}
                    >
                        {field.type === "dropdown" ? (
                            <Select
                                style={{ width: "100%" }}
                                defaultValue={field.defaultValue}
                            >
                                {field.options.map((option, optionIndex) => (
                                    <Select.Option
                                        key={optionIndex}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </Select.Option>
                                ))}
                            </Select>
                        ) : field.type === "tagfields" ? (
                            <Select
                                mode="multiple"
                                style={{ width: "100%" }}
                                placeholder="Select tags"
                                defaultValue={field.value} // Set defaultValue to the array of selected values
                                options={field.tagOptions.map(
                                    (option, index) => ({
                                        label: (
                                            <Space>
                                                <span
                                                    aria-label={option.label}
                                                    className={`badge-${option.value}`}
                                                    style={{
                                                        display: "block",
                                                        width: "14px",
                                                        height: "14px",
                                                        borderRadius: "50px",
                                                    }}
                                                ></span>
                                                {option.label}
                                            </Space>
                                        ),
                                        value: option.value,
                                    })
                                )}
                            />
                        ) : field.type === "datepicker" ? (
                            <DatePicker style={{ width: "100%" }} />
                        ) : field.type === "checkbox" ? (
                            <Checkbox
                                defaultChecked={
                                    field.value || field.defaultValue
                                }
                            ></Checkbox>
                        ) : //   : field.type === 'editableform' ? (
                        //       <div style={{display:'flex',flexWrap:'wrap'}}>
                        //     <EditableTable
                        //       options={options}
                        //       />
                        //     </div>
                        //   )
                        field.prefixDropdown ? (
                            <Input
                                addonBefore={
                                    <Select
                                        defaultValue={
                                            field.prefixOptionsValue ||
                                            field.prefixOptions[0].value
                                        }
                                    >
                                        {field.prefixOptions.map(
                                            (option, optionIndex) => (
                                                <Select.Option
                                                    key={optionIndex}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </Select.Option>
                                            )
                                        )}
                                    </Select>
                                }
                                style={{ width: "100%" }}
                                defaultValue={field.value || field.defaultValue}
                            />
                        ) : field.suffixDropdown ? (
                            <Input
                                addonAfter={
                                    <Select
                                        defaultValue={
                                            field.suffixOptionsValue ||
                                            field.suffixOptions[0].value
                                        }
                                    >
                                        {field.suffixOptions.map(
                                            (option, optionIndex) => (
                                                <Select.Option
                                                    key={optionIndex}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </Select.Option>
                                            )
                                        )}
                                    </Select>
                                }
                                style={{ width: "100%" }}
                                defaultValue={field.value || field.defaultValue}
                            />
                        ) : field.type === "textarea" ? (
                            <Input.TextArea
                                rows={4}
                                placeholder="Notes"
                                maxLength={6}
                            />
                        ) : (
                            <Input
                                type={field.type}
                                style={{ width: "100%" }}
                                initialvalue={field.defaultValue}
                            />
                        )}
                    </Form.Item>
                </div>
            ))}
        </div>
    </div>
));

const TagsForm = () => {
    const { id } = useParams();

    const [current, setCurrent] = useState(0);

    const next = () => {
        setCurrent(current + 1);
    };

    const prev = () => {
        setCurrent(current - 1);
    };
    const items = steps.map((item) => ({ key: item.title, title: item.title }));

    const [form] = Form.useForm();

    const [autoCompleteResult, setAutoCompleteResult] = useState([]);
    const onWebsiteChange = (value) => {
        if (!value) {
            setAutoCompleteResult([]);
        } else {
            setAutoCompleteResult(
                [".com", ".org", ".net", ".ai", ".in", ".co.in"].map(
                    (domain) => `${value}${domain}`
                )
            );
        }
    };
    const websiteOptions = autoCompleteResult.map((website) => ({
        label: website,
        value: website,
    }));
    const onFinish = (values) => {
        console.log("Received values from form:", values);
    };

    return (
        <>
            <Link to="/tags">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>
            <Form
                {...formItemLayout}
                form={form}
                name="tagsform"
                onFinish={onFinish}
                scrollToFirstError
            >
                <div>{steps[current]}</div>

                <div style={{ marginTop: 24 }}>
                    {current < steps.length - 1 && (
                        <Button type="primary" onClick={next}>
                            Next
                        </Button>
                    )}

                    {current === steps.length - 1 && (
                        <Button type="primary" htmlType="submit">
                            {id ? "Update" : "Create"}
                        </Button>
                    )}
                    {current > 0 && (
                        <Button style={{ margin: "0 8px" }} onClick={prev}>
                            Previous
                        </Button>
                    )}
                </div>
            </Form>
        </>
    );
};
export default TagsForm;
