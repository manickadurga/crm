import React, { useState, useEffect } from "react";
import {
    Button,
    Form,
    Input,
    Select,
    Tooltip,
    Upload,
    message,
    DatePicker,
} from "antd";
import {
    ArrowLeftOutlined,
    UploadOutlined,
    EyeOutlined,
    EyeInvisibleOutlined,
} from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import axios from "axios";
import dummyImg from "../../../../../public/assests/img/noprofile.png"; // Adjust path as needed

const { Option } = Select;

const gridStyle = {
    display: "grid",
    gridTemplateColumns: "repeat(auto-fit, minmax(250px, 1fr))",
    gap: "20px",
    marginBottom: "20px",
};

const gridItemStyle = {
    display: "flex",
    flexDirection: "column",
};

const formFields = [
    {
        fields: [
            { name: "first_name", label: "First Name", type: "text" },
            { name: "last_name", label: "Last Name", type: "text" },
            { name: "username", label: "Username", type: "text" },
            { name: "email", label: "Email", type: "email" },
            { name: "password", label: "Password", type: "password" },

            { name: "date", label: "Date", type: "datepicker" },
            { name: "reject_date", label: "Reject Date", type: "datepicker" },
            { name: "offer_date", label: "Offer Date", type: "datepicker" },
            { name: "accept_date", label: "Accept Date", type: "datepicker" },

            { name: "tags", label: "Tags", type: "tagfields" },
            { name: "description", label: "Description", type: "textarea" },
        ],
    },
];

const EmployeesForm = () => {
    const { id } = useParams();
    const [form] = Form.useForm();
    const [fileList, setFileList] = useState([]);
    const [item, setItem] = useState(null);
    const [imgUrl, setImgUrl] = useState("");
    const [tags, setTags] = useState([]);
    const [passwordVisible, setPasswordVisible] = useState(false);

    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/manage/${id}`)
                .then((response) => {
                    const data = response.data;
                    setItem(data);
                    form.setFieldsValue(data);
                    if (data.image) {
                        setFileList([
                            {
                                uid: "-1",
                                name: "image.png",
                                status: "done",
                                url: data.image,
                            },
                        ]);
                        setImgUrl(data.image);
                    }
                    if (data.tags) {
                        setTags(data.tags.map((tag) => tag.name));
                    }
                })
                .catch((error) => {
                    console.error("Error fetching item data:", error);
                });
        }
    }, [id, form]);

    const handleChange = (info) => {
        let newFileList = [...info.fileList];
        newFileList = newFileList.slice(-1);

        if (newFileList.length > 0) {
            const file = newFileList[0];
            if (file.originFileObj) {
                const maxSize = 2048; // 2 MB
                if (file.originFileObj.size / 1024 > maxSize) {
                    message.error("Image must be smaller than 2MB!");
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    setImgUrl(e.target.result);
                };
                reader.readAsDataURL(file.originFileObj);
            }
        } else {
            setImgUrl(dummyImg);
        }

        setFileList(newFileList);
    };

    const storeManageData = async (values) => {
        try {
            console.log("Sending data to store:", values);
            const response = await axios.post(
                "http://127.0.0.1:8000/api/manage",
                values
            );
            console.log("Manage stored successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error storing manage data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const updateManageData = async (id, values) => {
        try {
            console.log("Sending data to update:", values);
            const response = await axios.put(
                `http://127.0.0.1:8000/api/manage/${id}`,
                values
            );
            console.log("Manage updated successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error updating manage data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updateManageData(id, values);
            } else {
                await storeManageData(values);
            }
            console.log("Operation successful!");
        } catch (error) {
            console.error("Operation failed:", error);
        }
    };

    const handleTagChange = (value) => {
        setTags(value);
    };

    const steps = formFields.map((section, sectionIndex) => (
        <div key={sectionIndex}>
            <div style={gridStyle}>
                {section.fields.map((field, fieldIndex) => (
                    <div key={fieldIndex} style={gridItemStyle}>
                        <Form.Item
                            name={field.name}
                            label={field.label}
                            rules={field.rules}
                            className="form-item"
                        >
                            {field.type === "tagfields" ? (
                                <Select
                                    mode="multiple"
                                    style={{ width: "100%" }}
                                    placeholder="Select tags"
                                    onChange={handleTagChange}
                                    value={tags}
                                    options={[
                                        { label: "Tag 1", value: "tag1" },
                                        { label: "Tag 2", value: "tag2" },
                                        { label: "Tag 3", value: "tag3" },
                                    ]}
                                />
                            ) : field.type === "datepicker" ? (
                                <DatePicker style={{ width: "100%" }} />
                            ) : field.type === "textarea" ? (
                                <Input.TextArea rows={4} />
                            ) : field.type === "password" ? (
                                <Input.Password
                                    size="large"
                                    style={{ width: "100%" }}
                                    visibilityToggle={{
                                        visible: passwordVisible,
                                        onClick: () =>
                                            setPasswordVisible(
                                                !passwordVisible
                                            ),
                                    }}
                                    iconRender={(visible) =>
                                        visible ? (
                                            <EyeOutlined />
                                        ) : (
                                            <EyeInvisibleOutlined />
                                        )
                                    }
                                />
                            ) : (
                                <Input
                                    type={field.type}
                                    style={{ width: "100%" }}
                                />
                            )}
                        </Form.Item>
                    </div>
                ))}
            </div>
        </div>
    ));

    return (
        <>
            <Link to="/manage">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>
            <Form
                form={form}
                name="manageForm"
                onFinish={onFinish}
                layout="vertical"
                scrollToFirstError
                style={{
                    maxWidth: "800px",
                    margin: "0 auto",
                    padding: "20px",
                    backgroundColor: "#f7f7f7",
                    borderRadius: "10px",
                    boxShadow: "0 2px 8px rgba(0, 0, 0, 0.1)",
                }}
            >
                <Form.Item label="Profile Picture">
                    <Upload
                        listType="picture"
                        maxCount={1}
                        fileList={fileList}
                        onChange={handleChange}
                        beforeUpload={() => false}
                        showUploadList={false}
                    >
                        <img
                            src={imgUrl || dummyImg}
                            alt="profile"
                            style={{
                                width: 200,
                                height: 200,
                                borderRadius: "50%",
                                display: "block",
                                marginBottom: "10px",
                                objectFit: "cover",
                            }}
                        />
                        <Button type="button" icon={<UploadOutlined />}>
                            Click to Upload
                        </Button>
                    </Upload>
                </Form.Item>
                {steps}
                <Form.Item>
                    <Button type="primary" htmlType="submit" size="large">
                        {id ? "Update" : "Create"}
                    </Button>
                </Form.Item>
            </Form>
        </>
    );
};

export default EmployeesForm;
