import React, { useState, useEffect } from "react";
import {
    Space,
    Table,
    Input,
    Button,
    Modal,
    message,
    Card,
    Row,
    Col,
} from "antd";
import {
    SearchOutlined,
    EyeOutlined,
    UnorderedListOutlined,
    WindowsOutlined,
    DeleteOutlined,
    EditOutlined,
    ArrowLeftOutlined,
    PlusOutlined,
} from "@ant-design/icons";
import { getDataFunction } from "../../../API"; // Adjust API import if needed
import { Link } from "react-router-dom";

const Employees = () => {
    const [searchText, setSearchText] = useState({});
    const [dataSource, setDataSource] = useState([]);
    const [columns, setColumns] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalRecords, setTotalRecords] = useState(0);
    const [deleteModalVisible, setDeleteModalVisible] = useState(false);
    const [selectedItem, setSelectedItem] = useState(null);
    const [viewMode, setViewMode] = useState("table");

    useEffect(() => {
        fetchData(currentPage);
    }, [currentPage, searchText]);

    const fetchData = (page) => {
        setLoading(true);
        getDataFunction('employees',page)
            .then((res) => {
                const sortedData = res.Data || []; // Adjust to the correct field if needed
                const filteredData = sortedData.filter((item) =>
                    Object.keys(searchText).every((key) => {
                        const itemValue = key.includes(".")
                            ? key
                                  .split(".")
                                  .reduce((obj, k) => (obj || {})[k], item)
                            : item[key];
                        return (
                            itemValue &&
                            itemValue
                                .toString()
                                .toLowerCase()
                                .includes(searchText[key].toLowerCase())
                        );
                    })
                );

                setDataSource(filteredData);
                setColumns(
                    Object.keys(sortedData[0] || {}).map((key) => ({
                        title: (
                            <div>
                                {key}
                                <Input
                                    placeholder={`Search ${key}`}
                                    value={searchText[key] || ""}
                                    onChange={(e) =>
                                        handleSearch(e.target.value, key)
                                    }
                                    style={{ marginTop: 8, display: "block" }}
                                />
                            </div>
                        ),
                        dataIndex: key,
                        key: key,
                        render: (text) =>
                            typeof text === "object"
                                ? JSON.stringify(text)
                                : text,
                    }))
                );
                setTotalRecords(res.total || filteredData.length); // Adjust if `res.total` is available
                setLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
                setLoading(false);
            });
    };

    const handleSearch = (value, key) => {
        setSearchText((prevSearchText) => {
            const updatedSearchText = { ...prevSearchText };
            if (value === "") {
                delete updatedSearchText[key];
            } else {
                updatedSearchText[key] = value;
            }
            return updatedSearchText;
        });
    };

    const handlePageChange = (page) => {
        setCurrentPage(page);
    };

    const showDeleteModal = () => {
        setDeleteModalVisible(true);
    };

    const handleDelete = () => {
        // Call delete API here, then refresh the list
        message.success("Item deleted successfully!");
        fetchData(currentPage);
        setDeleteModalVisible(false);
        setSelectedItem(null);
    };

    const onRowClick = (record) => {
        setSelectedItem((prevSelected) =>
            prevSelected && prevSelected.id === record.id ? null : record
        );
    };

    const renderCards = () => (
        <Row gutter={16}>
            {dataSource.map((item) => (
                <Col key={item.id} span={6}>
                    <Card
                        title={item.name}
                        onClick={() => onRowClick(item)}
                        style={{
                            cursor: "pointer",
                            backgroundColor:
                                selectedItem && selectedItem.id === item.id
                                    ? "#f0f0f0"
                                    : "white",
                        }}
                    >
                        {Object.keys(item).map((key) => (
                            <p key={key}>
                                <strong>{key}:</strong>{" "}
                                {typeof item[key] === "object"
                                    ? JSON.stringify(item[key])
                                    : item[key]}
                            </p>
                        ))}
                    </Card>
                </Col>
            ))}
        </Row>
    );

    const renderColumns = () =>
        columns.map((column) => ({
            ...column,
            onCell: (record) => ({
                style: {
                    backgroundColor:
                        selectedItem && selectedItem.id === record.id
                            ? "#f0f0f0"
                            : "white",
                    cursor: "pointer",
                },
            }),
        }));

    return (
        <Row>
            <Col span={24}>
                <Space size={20} direction="vertical" style={{ width: "100%" }}>
                    <div
                        style={{
                            position: "sticky",
                            display: "flex",
                            justifyContent: "space-between",
                            gap: "1rem",
                            top: 0,
                            background: "#fff",
                            zIndex: 1,
                        }}
                    >
                        <div style={{ display: "flex", alignItems: "center" }}>
                            <Link to="/manage">
                                <Button
                                    shape="circle"
                                    htmlType="button"
                                    size="small"
                                >
                                    <ArrowLeftOutlined />
                                </Button>
                            </Link>
                            <b style={{ fontSize: "18px", marginLeft: "18px" }}>
                                Manage
                            </b>
                        </div>

                        <div
                            style={{
                                display: "flex",
                                fontSize: "18px",
                                marginLeft: "18px",
                                gap: "5px",
                            }}
                        >
                            <Button
                                type="primary"
                                htmlType="button"
                                onClick={() => setInviteModalVisible(true)}
                            >
                                Invite
                            </Button>
                            {selectedItem && (
                                <div style={{ gap: "2px" }}>
                                    <Link
                                        to={`/manage/view/${selectedItem.id}`}
                                    >
                                        <Button
                                            type="link"
                                            style={{
                                                marginRight: "2px",
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        >
                                            <EyeOutlined />
                                        </Button>
                                    </Link>
                                    <Link
                                        to={`/manage/edit/${selectedItem.id}`}
                                    >
                                        <Button
                                            type="link"
                                            style={{
                                                marginRight: "2px",
                                                border: "1px solid #ccc",
                                                background: "white",
                                            }}
                                        >
                                            <EditOutlined />
                                        </Button>
                                    </Link>
                                    <Button
                                        type="link"
                                        onClick={showDeleteModal}
                                        style={{
                                            marginRight: "2px",
                                            border: "1px solid #ccc",
                                            background: "white",
                                        }}
                                    >
                                        <DeleteOutlined />
                                    </Button>
                                </div>
                            )}

                            <Link to="/manage/createform">
                                <Button
                                    type="primary"
                                    htmlType="button"
                                    icon={<PlusOutlined />}
                                    style={{
                                        marginLeft: "10px",
                                        marginRight: "10px",
                                    }}
                                >
                                    Add
                                </Button>
                            </Link>
                            <Button
                                style={{ marginRight: "10px" }}
                                type={
                                    viewMode === "table" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("table")}
                            >
                                <WindowsOutlined />
                            </Button>
                            <Button
                                type={
                                    viewMode === "card" ? "primary" : "default"
                                }
                                onClick={() => setViewMode("card")}
                            >
                                <UnorderedListOutlined />
                            </Button>
                        </div>
                    </div>
                    <div style={{ overflowX: "scroll" }}>
                        {viewMode === "table" ? (
                            <Table
                                className="datatable manage-table"
                                loading={loading}
                                columns={renderColumns()}
                                dataSource={dataSource}
                                pagination={{
                                    current: currentPage,
                                    total: totalRecords,
                                    pageSize: 10,
                                    onChange: handlePageChange,
                                }}
                                rowKey={(record) => record.id}
                                onRow={(record) => ({
                                    onClick: () => onRowClick(record),
                                })}
                            />
                        ) : (
                            renderCards()
                        )}
                    </div>
                </Space>
            </Col>

            <Modal
                title="Confirm Delete"
                visible={deleteModalVisible}
                onOk={handleDelete}
                onCancel={() => setDeleteModalVisible(false)}
                okText="Delete"
                cancelText="Cancel"
            >
                <p>Are you sure you want to delete this item?</p>
            </Modal>
        </Row>
    );
};

export default Employees;
