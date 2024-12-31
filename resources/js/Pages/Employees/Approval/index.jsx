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
    List,
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
import { getDataFunction } from "../../../API";
import { Link } from "react-router-dom";

const Approvals = () => {
    const [searchText, setSearchText] = useState({});
    const [dataSource, setDataSource] = useState([]);
    const [columns, setColumns] = useState([]);
    const [loading, setLoading] = useState(false);
    const [currentPage, setCurrentPage] = useState(1);
    const [totalRecords, setTotalRecords] = useState(0);
    const [deleteModalVisible, setDeleteModalVisible] = useState(false);
    const [selectedApproval, setSelectedApproval] = useState(null);
    const [viewMode, setViewMode] = useState("table");
    const [selectedTagType, setSelectedTagType] = useState(null);
    const [selectedApprovalValues, setSelectedApprovalValues] = useState([]);

    useEffect(() => {
        fetchApproval(currentPage, selectedTagType);
    }, [currentPage, selectedTagType, searchText]);

    const fetchApproval = (page, tagType) => {
        setLoading(true);
        getDataFunction("approvals",page)
            .then((res) => {
                let sortedApproval = res.Approval;
                if (tagType) {
                    sortedApproval = sortedApproval.filter(
                        (approval) => approval.type === tagType
                    );
                }
                const filteredApproval = sortedApproval.filter((approval) =>
                    Object.keys(searchText).every((key) => {
                        const approvalValue = key.includes(".")
                            ? key
                                  .split(".")
                                  .reduce((obj, k) => (obj || {})[k], approval)
                            : approval[key];
                        return (
                            approvalValue &&
                            approvalValue
                                .toString()
                                .toLowerCase()
                                .includes(searchText[key].toLowerCase())
                        );
                    })
                );

                setDataSource(filteredApproval);
                setColumns(
                    Object.keys(sortedApproval[0] || {}).map((key) => ({
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
                        render: (text) => text,
                    }))
                );
                setTotalRecords(filteredApproval.length);
                setLoading(false);
            })
            .catch((error) => {
                console.error("Error fetching approvals:", error);
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
        message.success("Approval deleted successfully!");
        fetchApproval(currentPage, selectedTagType);
        setDeleteModalVisible(false);
        setSelectedApproval(null);
    };

    const onRowClick = (record) => {
        setSelectedApproval((prevSelectedApproval) =>
            prevSelectedApproval && prevSelectedApproval.id === record.id
                ? null
                : record
        );
    };

    const renderCards = () => (
        <Row gutter={16}>
            {dataSource.map((approval) => (
                <Col key={approval.id} span={6}>
                    <Card
                        title={approval.Name}
                        onClick={() => onRowClick(approval)}
                        style={{
                            cursor: "pointer",
                            backgroundColor:
                                selectedApproval &&
                                selectedApproval.id === approval.id
                                    ? "#f0f0f0"
                                    : "white",
                        }}
                    >
                        {Object.keys(approval).map((key) => (
                            <p key={key}>
                                <strong>{key}:</strong> {approval[key]}
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
                        selectedApproval && selectedApproval.id === record.id
                            ? "#f0f0f0"
                            : "white",
                    cursor: "pointer",
                },
            }),
        }));

    const handleTagTypeClick = (item) => {
        setSelectedTagType(item.type);
        setSelectedApprovalValues(item.values || []);
        setSearchText({});
    };

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
                            <Link to="/approvals">
                                <Button
                                    shape="circle"
                                    htmlType="button"
                                    size="small"
                                >
                                    <ArrowLeftOutlined />
                                </Button>
                            </Link>
                            <b style={{ fontSize: "18px", marginLeft: "18px" }}>
                                Approvals
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
                            {selectedApproval && (
                                <div style={{ gap: "2px" }}>
                                    <Link
                                        to={`/approvals/view/${selectedApproval.id}`}
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
                                        to={`/approvals/edit/${selectedApproval.id}`}
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

                            <Link to="/approvals/createform">
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
                                className="datatable approvals-table"
                                loading={loading}
                                columns={renderColumns()}
                                dataSource={dataSource}
                                pagination={{
                                    current: currentPage,
                                    pageSize: 10,
                                    total: totalRecords,
                                    onChange: handlePageChange,
                                }}
                                onRow={(record) => ({
                                    onClick: () => onRowClick(record),
                                    style: {
                                        backgroundColor:
                                            selectedApproval &&
                                            selectedApproval.id === record.id
                                                ? "#f0f0f0"
                                                : "white",
                                        cursor: "pointer",
                                    },
                                })}
                            />
                        ) : (
                            renderCards()
                        )}
                        <h4>Total Approvals: {totalRecords}</h4>

                        <Modal
                            title="Delete Confirmation"
                            open={deleteModalVisible}
                            onCancel={() => setDeleteModalVisible(false)}
                            onOk={handleDelete}
                            okText="Delete"
                            cancelText="Cancel"
                        >
                            Are you sure you want to delete the selected item?
                        </Modal>
                    </div>
                </Space>
            </Col>
        </Row>
    );
};

export default Approvals;
