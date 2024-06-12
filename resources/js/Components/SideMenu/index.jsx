import React, { useState, useEffect } from 'react';
import { Menu } from 'antd';
import { AppstoreOutlined, ShopOutlined, ShoppingCartOutlined, UserOutlined,
  FlagOutlined
 } from '@ant-design/icons';
import { useLocation, useNavigate } from 'react-router-dom';

const { SubMenu } = Menu;

const items = [
  { path: '/', icon: <AppstoreOutlined />, label: 'Dashboard' },
  { path: '/inventory', icon: <ShopOutlined />, label: 'Inventory',
    children: [
      { path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
      { path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales'}
    ] 
  },
  { icon: <UserOutlined />, label: 'Contacts',
    children: [
      { path:'/customers', icon:<ShoppingCartOutlined />, label: 'Customers' },
      { path:'/community', icon:<ShoppingCartOutlined />, label: 'Community'}
    ] 
  },
  { icon: <FlagOutlined />, label: 'Goals',
    children: [
      { path:'/goals', icon:<ShoppingCartOutlined />, label: 'Manage' },
      { path:'/community', icon:<ShoppingCartOutlined />, label: 'Community'}
    ] 
  },
  { icon: <AppstoreOutlined />, label: 'Invoices',
    children: [
      { path: '/invoices', icon: <ShoppingCartOutlined />, label: 'View Invoices' },
      { path: '/sales', icon: <ShoppingCartOutlined />, label: 'Sales' }
    ]
  }
];
// const items = [
//   { key: '1', path: '/', icon: <AppstoreOutlined />, label: 'Dashboard' },
//   { key: 'sub1', path: '/inventory', icon: <ShopOutlined />, label: 'Inventory',
//     children: [
//       { key:'2', path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
//       { key:'sub2', path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales',
//         children: [
//           { key:'3', path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
//           { key:'4', path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
//         ]
//       }
//     ] 
//   },
//   { key: 'sub3', path: '/inventory', icon: <ShopOutlined />, label: 'Inventory',
//     children: [
//       { key:'5', path:'/inventory', icon:<ShoppingCartOutlined />, label: 'Purchase' },
//       { key:'6', path:'/sales', icon:<ShoppingCartOutlined />, label: 'Sales', }
//     ]
//   }
// ];

const addKeys = (items) => {
  let keyCounter = 1;
  
  return items.map(item => {
      const newItem = { ...item, key: keyCounter.toString() };
      keyCounter++;
      if (newItem.children) {
          newItem.children = newItem.children.map((child, index) => {
              return { ...child, key: (keyCounter + index).toString() };
          });
          keyCounter += newItem.children.length;
      }
      return newItem;
  });
};

const itemsWithKeys = addKeys(items);
console.log("itemsWithKeys", itemsWithKeys);

function SideMenu() {
  const location = useLocation();
  const [selectedKeys, setSelectedKeys] = useState("/");

  useEffect(() => {
    const pathName = location.pathname;
    setSelectedKeys(pathName);
  }, [location.pathname]);

  const navigate = useNavigate();

  const renderSubMenu = (item) => {
    if (item.children && item.children.length > 0) {
      return (
        <SubMenu key={item.key} icon={item.icon} title={item.label} className='subMenu'>
          {item.children.map(child => (
            <Menu.Item key={child.key} onClick={() => navigate(child.path)}>
              {child.label}
            </Menu.Item>
          ))}
        </SubMenu>
      );
    } else {
      return (
        <Menu.Item key={item.key} icon={item.icon} onClick={() => navigate(item.path)}>
          {item.label}
        </Menu.Item>
      );
    }
  };

  return (
    <Menu
      className="SideMenuVertical"
      // mode="vertical"
      mode="inline"
      selectedKeys={[selectedKeys]}
      style={{
        minWidth:"180px",
        fontSize: "12px"
      }}
    >
      {itemsWithKeys.map(item => renderSubMenu(item))}
    </Menu>
  );
}

export default SideMenu;
