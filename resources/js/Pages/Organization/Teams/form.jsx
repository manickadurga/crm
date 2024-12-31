import React, { useState, useEffect } from "react";
import {
    Button,
    Form,
    Input,
    Select,
    Tooltip,
    Upload,
    Image,
    message,
} from "antd";
import { ArrowLeftOutlined, UploadOutlined } from "@ant-design/icons";
import { Link, useParams } from "react-router-dom";
import dummyImg from "../../../../../public/assests/img/noprofile.png";
import axios from "axios";

// Define the path to a dummy image
//const dummyImg = "https://via.placeholder.com/150";

const { Option } = Select;

const formItemLayout = {
    labelCol: { xs: { span: 24 }, sm: { span: 8 } },
    wrapperCol: { xs: { span: 24 }, sm: { span: 16 } },
};

const TeamsForm = () => {
    const { id } = useParams();
    const [form] = Form.useForm();
    const [current, setCurrent] = useState(0);
    const [fileList, setFileList] = useState([]);
    const [team, setTeam] = useState(null);
    const [imgUrl, setImgUrl] = useState("");

    // Fetch existing team data if updating
    useEffect(() => {
        if (id) {
            axios
                .get(`http://127.0.0.1:8000/api/teams/${id}`)
                .then((response) => {
                    setTeam(response.data);
                    form.setFieldsValue(response.data);
                    if (response.data.image) {
                        setFileList([
                            {
                                uid: "-1",
                                name: "image.png",
                                status: "done",
                                url: response.data.image,
                            },
                        ]);
                        setImgUrl(response.data.image);
                    }
                })
                .catch((error) => {
                    console.error("Error fetching team data:", error);
                });
        }
    }, [id, form]);

    const next = () => setCurrent(current + 1);
    const prev = () => setCurrent(current - 1);

    const storeTeamData = async (values) => {
        try {
            console.log("Sending data to store:", values);
            const response = await axios.post(
                "http://127.0.0.1:8000/api/Teams",
                values
            );
            console.log("Team stored successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error storing team data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    const updateTeamData = async (id, values) => {
        try {
            console.log("Sending data to update:", values);
            const response = await axios.put(
                `http://127.0.0.1:8000/api/Teams/${id}`,
                values
            );
            console.log("Team updated successfully:", response.data);
            return response.data;
        } catch (error) {
            console.error("Error updating team data:", error);
            if (error.response && error.response.status === 422) {
                console.error("Validation errors:", error.response.data.errors);
            }
            throw error;
        }
    };

    useEffect(() => {
        if (team && id) {
            form.setFieldsValue(team);
        } else {
            form.resetFields();
        }
    }, [team, id, form]);

    const convertToPNG = async (imageUrl) => {
        const img = document.createElement("img");
        img.crossOrigin = "Anonymous";

        return new Promise((resolve, reject) => {
            img.onload = () => {
                const canvas = document.createElement("canvas");
                canvas.width = img.width;
                canvas.height = img.height;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0);

                canvas.toBlob((blob) => {
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        resolve(reader.result);
                    };
                    reader.readAsDataURL(blob);
                }, "image/png");
            };

            img.onerror = () => {
                reject(new Error("Failed to load image"));
            };

            img.src = imageUrl;
        });
    };

    const handleChange = async (info) => {
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
                reader.onload = async (e) => {
                    try {
                        const convertedImage = await convertToPNG(
                            e.target.result
                        );
                        setImgUrl(convertedImage);
                    } catch (error) {
                        console.error("Error converting image:", error);
                        setImgUrl(dummyImg);
                    }
                };
                reader.readAsDataURL(file.originFileObj);
            }
        } else {
            setImgUrl(dummyImg);
        }

        setFileList(newFileList);
    };

    const onFinish = async (values) => {
        try {
            if (id) {
                await updateTeamData(id, values);
            } else {
                await storeTeamData(values);
            }
        } catch (error) {
            console.error("Error during form submission:", error);
        }
    };

    return (
        <>
            <Link to="/teams">
                <Tooltip title="Back" placement="right">
                    <Button shape="circle" htmlType="button">
                        <ArrowLeftOutlined />
                    </Button>
                </Tooltip>
            </Link>

            <Form
                {...formItemLayout}
                form={form}
                name="teamForm"
                onFinish={onFinish}
                scrollToFirstError
            >
                <Upload
                    listType="picture"
                    maxCount={1}
                    fileList={fileList}
                    onChange={handleChange}
                    beforeUpload={() => false} // Prevent auto-upload
                    showUploadList={false}
                >
                    <img
                        src={imgUrl || dummyImg}
                        alt="image"
                        style={{
                            width: 200,
                            height: 200,
                            borderRadius: 10,
                        }}
                    />
                    <Button type="button" icon={<UploadOutlined />}>
                        Click to Upload
                    </Button>
                </Upload>
                <Form.Item
                    name="team_name"
                    label="Team Name"
                    labelCol={{ span: 6 }}
                    wrapperCol={{ span: 6 }}
                    rules={[
                        {
                            required: true,
                            message: "Please fill in the team's name!",
                        },
                    ]}
                >
                    <Input />
                </Form.Item>

                <Form.Item
                    name="add_or_remove_projects"
                    label="Add or Remove Projects"
                    labelCol={{ span: 6 }}
                    wrapperCol={{ span: 6 }}
                    rules={[
                        {
                            required: true,
                            message: "Please select the team's project!",
                        },
                    ]}
                >
                    <Select mode="multiple" placeholder="Select projects">
                        <Option value="Open Source">Open Source</Option>
                        <Option value="Website">Website</Option>
                        <Option value="Platform SaaS">Platform SaaS</Option>
                        <Option value="Testlara">Testlara</Option>
                    </Select>
                </Form.Item>

                <Form.Item
                    name="add_or_remove_managers"
                    label="Add or Remove Managers"
                    labelCol={{ span: 6 }}
                    wrapperCol={{ span: 6 }}
                    rules={[
                        {
                            required: true,
                            message: "Please select the team's managers!",
                        },
                    ]}
                >
                    <Select mode="multiple" placeholder="Select managers">
                        <Option value="Ruslan">Ruslan</Option>
                        <Option value="Booster">Booster</Option>
                        <Option value="Aster">Aster</Option>
                    </Select>
                </Form.Item>

                <Form.Item
                    name="add_or_remove_members"
                    label="Add or Remove Members"
                    labelCol={{ span: 6 }}
                    wrapperCol={{ span: 6 }}
                    rules={[
                        {
                            required: true,
                            message: "Please select the team's members!",
                        },
                    ]}
                >
                    <Select mode="multiple" placeholder="Select members">
                        <Option value="Default Employee">
                            Default Employee
                        </Option>
                        <Option value="Alish">Alish</Option>
                        <Option value="Booster">Booster</Option>
                        <Option value="Aster">Aster</Option>
                    </Select>
                </Form.Item>

                <Form.Item
                    name="tags"
                    label="Tags"
                    labelCol={{ span: 6 }}
                    wrapperCol={{ span: 6 }}
                >
                    <Select mode="multiple" placeholder="Select tags">
                        <Option value="VIP">VIP</Option>
                        <Option value="Urgent">Urgent</Option>
                        <Option value="Crazy">Crazy</Option>
                        <Option value="Broken">Broken</Option>
                        <Option value="Completed">Completed</Option>
                        <Option value="In process">In process</Option>
                    </Select>
                </Form.Item>

                <div style={{ marginTop: 24 }}>
                    <Button type="primary" htmlType="submit">
                        {id ? "Update" : "Create"}
                    </Button>
                    {current > 0 && (
                        <Button style={{ margin: "0 8px" }} onClick={prev}>
                            Previous
                        </Button>
                    )}
                    {current < 1 && (
                        <Button
                            type="primary"
                            onClick={next}
                            style={{ marginLeft: "10px", marginRight: "10px" }}
                        >
                            Next
                        </Button>
                    )}
                </div>
            </Form>
        </>
    );
};

export default TeamsForm;
